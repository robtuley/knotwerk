<?php
class T_Test_Template_Composite extends T_Test_View_Composite
{

    protected $basic;

    function setUpSuite()
    {
        $this->basic = T_CACHE_DIR.'test'.md5(uniqid(rand(),true));
        file_put_contents($this->basic,"A basic template");
    }

    function teardownSuite()
    {
        unlink($this->basic);
    }

    function setUp()
    {
        parent::setUp();
        $this->composite = new T_Template_Composite();
    }

    protected function getBasicTemplate()
    {
        return new T_Template_File($this->basic);
    }

    // tests

    function testNoParentsByDefault()
    {
        $this->assertSame($this->composite->getParents(),array());
    }

    function testsIsParentFalseWithNoParents()
    {
        $child  = $this->composite;
        $parent = $this->getBasicTemplate();
        $this->assertFalse($child->isParent($parent));
    }

    function testAddSingleParentToTemplate()
    {
        $child  = $this->composite;
        $parent = $this->getBasicTemplate();
        $child->addParent($parent);
        $this->assertTrue($child->isParent($parent));
        $this->assertSame(array($parent),$child->getParents());
    }

    function testAddMultipleParentToTemplate()
    {
        $child  = $this->composite;
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
        $child  = $this->composite;
        $parent = $this->getBasicTemplate();
        $child->addParent($parent);
        $child->addParent($parent);
        $this->assertSame(array($parent),$child->getParents());
    }

    function testDoNotAddParentsAlreadyLinkedThroughExistingParents()
    {
        $child  = $this->composite;
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
        $this->assertFalse($this->composite->isHelper('test'));
    }

    function testAddingAndAccessToSingleHelper()
    {
        $helper = 'helper';
        $this->assertFalse($this->composite->isHelper('test'));
        $this->composite->addHelper($helper,'test');
        $this->assertTrue($this->composite->isHelper('test'));
        $this->assertSame($this->composite->getHelper('test'),$helper);
    }

    function testAddingAndAccessToMultipleHelper()
    {
        $tpl = $this->composite;
        $helper1 = 'helper1';
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
        $child  = $this->composite;
        $parent = $this->getBasicTemplate();
        $helper1 = 'helper1';
        $helper2 = 'helper2';
        $parent->addHelper($helper1,'test');
        $child->addParent($parent);
        $child->addHelper($helper2,'test');
        $this->assertTrue($child->isHelper('test'));
        $this->assertSame($child->getHelper('test'),$helper2);
    }

    function testGetHelperFailureWhenNoHelper()
    {
        try {
            $helper = $this->composite->getHelper('test');
            $this->fail();
        } catch (InvalidArgumentException $e) {}
    }

    function testSetAttributeIsDisabled()
    {
        try {
            $this->composite->test = 'Test String';
            $this->fail();
        } catch (BadMethodCallException $e) { }
    }

    function testTemplateAddChildGetsParentAdded()
    {
        $child  = $this->getBasicTemplate();
        $this->composite->addChild($child);
        $this->assertTrue($child->isParent($this->composite));
    }

    function testTemplatePrependChildGetsParentAdded()
    {
        $child  = $this->getBasicTemplate();
        $this->composite->prependChild($child);
        $this->assertTrue($child->isParent($this->composite));
    }

    function testPrependSingleChild()
    {
        $this->composite->prependChild($this->getViewLeaf());
        $this->assertTrue($this->composite->isChildren());
    }

    function testPrependChildMethodHasAFluentInterface()
    {
        $child  = $this->getViewLeaf();
        $test = $this->composite->prependChild($child);
        $this->assertSame($this->composite,$test);
    }

    function testRetrieveChildWithKeywordWhenPrepended()
    {
        $child = $this->getViewLeaf();
        $this->composite->prependChild($child,'test');
        $this->assertTrue($this->composite->isChildren());
        $this->assertSame($child,$this->composite->test);
    }

    function testOutputsSinglePrependedChildContentOnRender()
    {
        $child = new T_Test_CompositeStub('somecontent');
        $this->composite->prependChild($child);
        $this->assertSame($this->composite->__toString(),$child->__toString());
    }

    function testPrependChildRenderAtStart()
    {
        $child1 = new T_Test_CompositeStub('first');
        $child2 = new T_Test_CompositeStub('second');
        $this->composite->addChild($child1);
        $this->composite->prependChild($child2);
        $expected = $child2->__toString().$child1->__toString();
        $this->assertSame($this->composite->__toString(),$expected);
    }

    function testRemoveChild()
    {
        $this->composite->addChild($this->getViewLeaf(),'test');
        $this->assertTrue($this->composite->isChildren());
        $this->composite->removeChild('test');
        $this->assertFalse($this->composite->isChildren());
    }

    function testNoErrorWhenRemoveNonExistingChild()
    {
        $child = $this->getViewLeaf();
        $this->composite->addChild($child,'test');
        $this->assertTrue($this->composite->isChildren());
        $this->composite->removeChild('notachild');
        $this->assertTrue($this->composite->isChildren());
        $this->assertSame($child,$this->composite->test);
    }

    function testRemovingChildLeavesOthersInPlace()
    {
        $this->composite->addChild($this->getViewLeaf(),'test0');
        $child = $this->getViewLeaf();
        $this->composite->addChild($child,'test1');
        $this->composite->removeChild('test0');
        $this->assertSame($child,$this->composite->test1);
    }

    function testRemoveChildHasAFluentInterface()
    {
        $test = $this->composite->removeChild('test');
        $this->assertSame($this->composite,$test);
    }

}
