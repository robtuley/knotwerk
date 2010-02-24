<?php
/**
 * Unit test cases for the T_Text_Element classes.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_Element unit test cases.
 *
 * @package wikiTests
 * @license http://knotwerk.com/licence MIT
 */
abstract class T_Test_Text_ElementWithContentHarness extends T_Unit_Case
{

    /**
     * Get element to test.
     *
     * @param string $content  content
     * @return T_Text_Element  element to test
     */
    abstract protected function getElement($content=null);

    function testContentIsNullByDefault()
    {
        $this->assertTrue(is_null($this->getElement()->getContent()));
    }

    function testContentCanBeSetInConstructor()
    {
        $this->assertSame('content',$this->getElement('content')->getContent());
    }

    function testSetContentMethodHasAFluentInterface()
    {
        $element = $this->getElement('content');
        $test = $element->setContent('different');
        $this->assertSame($element,$test);
    }

    function testContentCanBeChanged()
    {
        $element = $this->getElement('content')->setContent('different');
        $this->assertSame('different',$element->getContent());
    }

    function testContentCanBeFilteredOnRetrieval()
    {
        $text = $this->getElement('content');
        $filter = new T_Test_Filter_Suffix();
        $this->assertSame($filter->transform('content'),$text->getContent($filter));
    }

    function testImplementsCompositeInterface()
    {
        $this->assertTrue($this->getElement() instanceof T_Composite);
    }

    function testGetCompositeReturnsAReferenceToSelf()
    {
        $text = $this->getElement();
        $this->assertSame($text,$text->getComposite());
    }

    function testAddChildMethodHasAFluentInterface()
    {
        $element = $this->getElement();
        $test = $element->addChild($this->getElement());
        $this->assertSame($element,$test);
    }

    function testIsChildrenReturnsFalseWhenNoChildren()
    {
        $this->assertFalse($this->getElement()->isChildren());
    }

    function testIsChildrenReturnsTrueWhenSingleChild()
    {
        $element = $this->getElement()->addChild($this->getElement());
        $this->assertTrue($element->isChildren());
    }

    function testIsChildrenReturnsTrueWithMultipleChildren()
    {
        $element = $this->getElement()->addChild($this->getElement())
                                      ->addChild($this->getElement());
        $this->assertTrue($element->isChildren());
    }

    function testNoIterationIfNoChildren()
    {
        $element = $this->getElement();
        foreach ($element as $child) {
            $this->fail();
        }
    }

    function testCanAddSingleChildWithExplicitKey()
    {
        $parent = $this->getElement('parent');
        $child = $this->getElement('child');
        $parent->addChild($child,'thekey');
        $i = 0;
        foreach ($parent as $key => $c) {
            $this->assertSame($key,'thekey');
            $this->assertSame($child,$c);
            $i++;
        }
        $this->assertSame(1,$i);
        $this->assertSame($child,$parent->thekey);
    }

    function testCanAddSingleChildWithNoKey()
    {
        $parent = $this->getElement('parent');
        $child = $this->getElement('child');
        $parent->addChild($child);
        $i = 0;
        foreach ($parent as $key => $c) {
            $this->assertSame($key,0);
            $this->assertSame($child,$c);
            $i++;
        }
        $this->assertSame(1,$i);
    }

    function testCanAddMultipleChildren()
    {
        $parent = $this->getElement('parent');
        $child1 = $this->getElement('child1');
        $child2 = $this->getElement('child2');
        $parent->addChild($child1,'thekey')
               ->addChild($child2);
        $i = 0;
        $expect = array('thekey'=>$child1,$child2);
        foreach ($parent as $key => $c) {
            $this->assertSame($expect[$key],$c);
            $i++;
        }
        $this->assertSame(2,$i);
    }

    function testCanIterateRepeatedlyOverChildren()
    {
        $parent = $this->getElement('parent');
        $child1 = $this->getElement('child1');
        $child2 = $this->getElement('child2');
        $parent->addChild($child1,'thekey')
               ->addChild($child2);
        for ($n=0;$n<=3;$n++) {
            $i = 0;
            $expect = array('thekey'=>$child1,$child2);
            foreach ($parent as $key => $c) {
                $this->assertSame($expect[$key],$c);
                $i++;
            }
            $this->assertSame(2,$i);
        }
    }

    function testChildAccessByPropertyFailsWhenChildNotSet()
    {
        $element = $this->getElement();
        try {
            $element->notachild;
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testCompositeVisitsItselfOnlyWithNoChildren()
    {
        $visitor = new T_Test_VisitorStub();
        $element = $this->getElement();
        $element->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$element);
        $this->assertTrue(count($visited['object'])==1);
    }

    function testCompositeCallsCorrectVisitorMethodWithNoChildren()
    {
        $visitor = new T_Test_VisitorStub();
        $element = $this->getElement();
        $element->accept($visitor);
        $visited = $visitor->getVisited();
        $name = explode('_',get_class($element));
        array_shift($name);
        $this->assertSame($visited['method'][0],'visit'.implode('',$name));
    }

    function testCompositeVisitedAtCorrectDepthWithNoChildren()
    {
        $visitor = new T_Test_VisitorStub();
        $this->getElement()->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['depth'][0],0);
    }

    function testCompositeVisitsItselfAndChildrenWhenTraverseOn()
    {
        $parent = $this->getElement('parent');
        $child1 = $this->getElement('child1');
        $child2 = $this->getElement('child2');
        $parent->addChild($child1)->addChild($child2);
        $visitor = new T_Test_VisitorStub();
        $parent->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertTrue(count($visited['object'])==3);
        $this->assertSame($visited['object'][0],$parent);
        $this->assertSame($visited['object'][1],$child1);
        $this->assertSame($visited['object'][2],$child2);
        $this->assertSame($visited['depth'][0],0);
        $this->assertSame($visited['depth'][1],1);
        $this->assertSame($visited['depth'][2],1);
    }

    function testCompositeVisitsItselfNotChildrenWhenTraverseOff()
    {
        $parent = $this->getElement('parent');
        $child1 = $this->getElement('child1');
        $child2 = $this->getElement('child2');
        $parent->addChild($child1)->addChild($child2);
        $visitor = new T_Test_VisitorStub();
        $visitor->setTraverseChildren(false);
        $parent->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertTrue(count($visited['object'])==1);
        $this->assertSame($visited['object'][0],$parent);
        $this->assertSame($visited['depth'][0],0);
    }

    function testThatIsContainedByReturnsFalseByDefault()
    {
        $this->assertFalse($this->getElement()->isContainedBy('classname'));
    }

    function testThatSetParentHasAFluentInterface()
    {
        $element = $this->getElement();
        $test = $element->setParent('AWikiElement');
        $this->assertSame($test,$element);
    }

    function testThatSetParentChangesIsContainedByResponse()
    {
        $child = $this->getElement();
        $this->assertFalse($child->isContainedBy('AWikiElement'));
        $child->setParent('AWikiElement');
        $this->assertTrue($child->isContainedBy('AWikiElement'));
        $this->assertFalse($child->isContainedBy('notaclassname'));
    }

    function testThatChildCanHaveANumberOfParents()
    {
        $child = $this->getElement();
        $child->setParent('AWikiElement');
        $child->setParent('BWikiElement');
        $this->assertTrue($child->isContainedBy('AWikiElement'));
        $this->assertTrue($child->isContainedBy('BWikiElement'));
        $this->assertFalse($child->isContainedBy('notaclassname'));
    }

    function testThatSetParentIsCalledWhenChildIsAdded()
    {
        $parent = $this->getElement();
        $child = $this->getElement();
        $parent->addChild($child);
        $this->assertTrue($child->isContainedBy(get_class($parent)));
    }

    abstract function testContentCanBeRetrievedByStringMagicMethod();

    abstract function testChildContentIncludedInToStringMagicOutput();

}