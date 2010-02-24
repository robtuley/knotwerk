<?php
class T_Test_Template_File extends T_Unit_Case
{

    protected $basic;

    function setUpSuite()
    {
        $this->basic = T_CACHE_DIR.'test'.md5(uniqid(rand(),true)).'.php';
        $data = 'a basic template with <?= $this->test; ?>';
        file_put_contents($this->basic,$data);
    }

    function teardownSuite()
    {
        unlink($this->basic);
    }

    protected function getBasicTemplate()
    {
        return new T_Template_File($this->basic);
    }

    protected function getBasicName()
    {
        return _first(explode('.',basename($this->basic)));
    }

    protected function getNotExistingTemplate()
    {
        return new T_Template_File('notatemplate');
    }

    function sampleHelper($arg1='1',$arg2='2')
    {
        return md5($arg1.$arg2);
    }

    // tests

    function testConstructorFailureWithNotPresentTemplateFile()
    {
        try {
            $tpl = $this->getNotExistingTemplate();
            $this->fail();
        } catch (RuntimeException $e) {}
    }

    function testNoParentsByDefault()
    {
        $tpl = $this->getBasicTemplate();
        $this->assertSame($tpl->getParents(),array());
    }

    function testsIsParentFalseWithNoParents()
    {
        $child  = $this->getBasicTemplate();
        $parent = $this->getBasicTemplate();
        $this->assertFalse($child->isParent($parent));
    }

    function testAddSingleParentToTemplate()
    {
        $child  = $this->getBasicTemplate();
        $parent = $this->getBasicTemplate();
        $child->addParent($parent);
        $this->assertTrue($child->isParent($parent));
        $this->assertSame(array($parent),$child->getParents());
    }

    function testAddMultipleParentToTemplate()
    {
        $child  = $this->getBasicTemplate();
        $parent1 = $this->getBasicTemplate();
        $parent2 = $this->getBasicTemplate();
        $child->addParent($parent1);
        $child->addParent($parent2);
        $this->assertTrue($child->isParent($parent1));
        $this->assertTrue($child->isParent($parent2));
        $this->assertSame(array($parent1,$parent2),$child->getParents());
    }

    function testDoNotAddRepeatedParents()
    {
        $child  = $this->getBasicTemplate();
        $parent = $this->getBasicTemplate();
        $child->addParent($parent);
        $child->addParent($parent);
        $this->assertSame(array($parent),$child->getParents());
    }

    function testDoNotAddParentsAlreadyLinkedThroughExistingParents()
    {
        $child  = $this->getBasicTemplate();
        $parent1 = $this->getBasicTemplate();
        $parent2 = $this->getBasicTemplate();
        $parent2->addParent($parent1);
        $child->addParent($parent2);
        $child->addParent($parent1);
        // parent1
        //   |-- parent2     parent1 (not added as already connected
        //         |-- child --|         through parent2)
        $this->assertTrue($child->isParent($parent1));
        $this->assertTrue($child->isParent($parent2));
        $this->assertSame(array($parent2),$child->getParents());
    }

    function testsIsHelperFalseWithNoHelperAvailable()
    {
        $tpl = $this->getBasicTemplate();
        $this->assertFalse($tpl->isHelper('test'));
    }

    function testAddingAndAccessToSingleHelper()
    {
        $tpl = $this->getBasicTemplate();
        $helper = array($this,'sampleHelper');
        $this->assertFalse($tpl->isHelper('test'));
        $tpl->addHelper($helper,'test');
        $this->assertTrue($tpl->isHelper('test'));
        $this->assertSame($tpl->getHelper('test'),$helper);
    }

    function testAddingAndAccessToMultipleHelper()
    {
        $tpl = $this->getBasicTemplate();
        $helper1 = array($this,'sampleHelper');
        $helper2 = 'helper2';
        $tpl->addHelper($helper1,'test1');
        $tpl->addHelper($helper2,'test2');
        $this->assertTrue($tpl->isHelper('test1'));
        $this->assertTrue($tpl->isHelper('test2'));
        $this->assertSame($tpl->getHelper('test1'),$helper1);
        $this->assertSame($tpl->getHelper('test2'),$helper2);
    }

    function testChildHelperOverridesParentHelper()
    {
        $child  = $this->getBasicTemplate();
        $parent = $this->getBasicTemplate();
        $helper1 = 'helper1';
        $helper2 = array($this,'sampleHelper');
        $parent->addHelper($helper1,'test');
        $child->addParent($parent);
        $child->addHelper($helper2,'test');
        $this->assertTrue($child->isHelper('test'));
        $this->assertSame($child->getHelper('test'),$helper2);
    }

    function testGetHelperFailureWhenNoHelper()
    {
        $tpl = $this->getBasicTemplate();
        try {
            $helper = $tpl->getHelper('test');
            $this->fail();
        } catch (InvalidArgumentException $e) {}
    }

    function testSetAndRetrieveScalarAttribute()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = 'Test String';
        $this->assertSame($tpl->test,'Test String');
    }

    function testSetAndRetrieveArrayAttribute()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = array('Test','String');
        $this->assertSame($tpl->test,array('Test','String'));
    }

    /**
     * Tests can set and retrieve array by reference.
     *
     * By default, __get method returns a *copy* to the value, so modifying
     * it results in not changes to the object itself, and you cannot foreach
     * over it without a warning being thrown. Check that this is modified in
     * this case so that the error is suppressed as an *explicit* copy is
     * returned. This has been fixed in later versions of PHP (>5.2.?) so could
     * be modified later.
     */
    function testAttributeArrayIsReadOnly()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = array('Test','String');
        foreach($tpl->test as $value) {
            // dummy foreach!
        }
        $this->assertSame($tpl->test,array('Test','String'));
    }

    function testAttributeRetrieveFailureWhenNotExists()
    {
        $tpl = $this->getBasicTemplate();
        try {
            $value = $tpl->test;
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testTemplateAttributeGetsParentAdded()
    {
        $child  = $this->getBasicTemplate();
        $parent = $this->getBasicTemplate();
        $parent->anattribute = $child;
        $this->assertTrue($child->isParent($parent));
    }

    function testExecutingHelperShortcutWithNoArgs()
    {
        $tpl = $this->getBasicTemplate();
        $helper = array($this,'sampleHelper');
        $tpl->addHelper($helper,'test');
        $this->assertSame($tpl->test(),$this->sampleHelper());
    }

    function testExecutingHelperShortcutWithSomeArgs()
    {
        $tpl = $this->getBasicTemplate();
        $helper = array($this,'sampleHelper');
        $tpl->addHelper($helper,'test');
        $this->assertSame($tpl->test('a','b'),$this->sampleHelper('a','b'));
    }

    function testHelperShortcutFailureIfNoHelper()
    {
        $tpl = $this->getBasicTemplate();
        try {
            $tpl->test();
            $this->fail();
        } catch(InvalidArgumentException $e) { }
    }

    function testTemplateCanBeRenderedAsString()
    {
        $tpl = $this->getBasicTemplate();
        $expected = 'a basic template with an attribute';
        $tpl->test = 'an attribute';
        $content = $tpl->__toString();
        $this->assertSame($expected,$content);
    }

    function testTemplateCanBeSentStraightToBuffer()
    {
        $tpl = $this->getBasicTemplate();
        $expected = 'a basic template with an attribute';
        $tpl->test = 'an attribute';
        ob_start();
        $test = $tpl->toBuffer();
        $this->assertSame($tpl,$test,'fluent interface');
        $content = ob_get_clean();
        $this->assertSame($expected,$content);
    }

    function testIssetCanBeUsedToQueryAttributeState()
    {
        $tpl = $this->getBasicTemplate();
        $this->assertFalse(isset($tpl->test));
        $tpl->test = 'a value';
        $this->assertTrue(isset($tpl->test));
    }

    function testUnsetCanBeUsedToRemoveAttribute()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = 'a value';
        unset($tpl->test);
        $this->assertFalse(isset($tpl->test));
    }

    function testTemplateIsNotACompositeObject()
    {
        $tpl = $this->getBasicTemplate();
        $this->assertSame($tpl->getComposite(),null);
    }

    function testCanSendPlainStringToBuffer()
    {
        $view = $this->getBasicTemplate();
        ob_start();
        $view->buffer('test');
        $this->assertSame('test',ob_get_clean());
    }

    function testCanSendViewObjectToBuffer()
    {
        $view = $this->getBasicTemplate();
        $data = array(array(1,2,3),array(4,5,6));
        $csv = new T_View_Csv($data);
        ob_start();
        $view->buffer($csv);
        $this->assertSame($csv->__toString(),ob_get_clean());
    }

    function testPartialHelperRendersASubTemplate()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = 'parent';
        // get template name
        $name = $this->getBasicName();
        $params = array('test'=>'child');
        $expected = 'a basic template with child';
        ob_start();
        $test = $tpl->partial($name,$params);
        $this->assertSame($tpl,$test,'fluent interface');
        $content = ob_get_clean();
        $this->assertSame($expected,$content);
    }

    function testPartialHelperCanFallbackToUseAbsolutePath()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = 'parent';
        $params = array('test'=>'child');
        $expected = 'a basic template with child';
        ob_start();
        $test = $tpl->partial($this->basic,$params);
        $this->assertSame($tpl,$test,'fluent interface');
        $content = ob_get_clean();
        $this->assertSame($expected,$content);
    }

    function testLoopHelperIteratesOverASubTemplate()
    {
        $tpl = $this->getBasicTemplate();
        $tpl->test = 'parent';
        // get template name
        $name = $this->getBasicName();
        $iterator = array('1','2','3');
        $expected = '';
        foreach ($iterator as $val) {
            $expected .= 'a basic template with '.$val;
        }
        ob_start();
        $test = $tpl->loop($name,'test',$iterator);
        $this->assertSame($tpl,$test,'fluent interface');
        $content = ob_get_clean();
        $this->assertSame($expected,$content);
    }

}
