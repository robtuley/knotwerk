<?php
class T_Test_Xhtml_List extends T_Unit_Case
{

    protected $visitor;
    protected $indent = '    ';

    function setUp()
    {
        $this->visitor = new T_Xhtml_List();
    }

    function testVisitCompositeWithNoChildren()
    {
        $composite = new T_Test_CompositeStub('content');
        ob_start();
        $composite->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();
        $this->assertSame($xhtml,EOL.'content');
    }

    function testVisitCompositeWithOneSubTreeLayer()
    {
        $parent = new T_Test_CompositeStub('parent');
        $child1 = new T_Test_CompositeStub('child1');
        $child2 = new T_Test_CompositeStub('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   'parent'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li>child1</li>'.EOL.
                   $this->indent.'<li>child2</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testVisitCompositeWithTwoSubTreeLayers()
    {
        $parent = new T_Test_CompositeStub('parent');
        $child1 = new T_Test_CompositeStub('child1');
        $childchild1 = new T_Test_CompositeStub('childchild1');
        $childchild2 = new T_Test_CompositeStub('childchild2');
        $child2 = new T_Test_CompositeStub('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);
        $child1->addChild($childchild1);
        $child1->addChild($childchild2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   'parent'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li>child1'.EOL.
                   $this->indent.'<ul>'.EOL.
                   $this->indent.$this->indent.'<li>childchild1</li>'.EOL.
                   $this->indent.$this->indent.'<li>childchild2</li>'.EOL.
                   $this->indent.'</ul>'.EOL.
                   $this->indent.'</li>'.EOL.
                   $this->indent.'<li>child2</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testReuseVisitor()
    {
        $this->testVisitCompositeWithTwoSubTreeLayers();
        $this->testVisitCompositeWithTwoSubTreeLayers();
    }

    function testMaxDepthNoEffect()
    {
        $this->visitor->setMaxDepth(3);
        $this->testVisitCompositeWithTwoSubTreeLayers();
    }

    function testTraverseChildrenTrueByDefault()
    {
        $this->assertTrue($this->visitor->isTraverseChildren());
    }

    function testTraverseChildrenBeforeReachMaxDepth()
    {
        $this->visitor->setMaxDepth(1); // 1 == 1 nested level
        $this->assertTrue($this->visitor->isTraverseChildren());
    }

    function testNotTraverseChildrenWhenReachMaxDepth()
    {
        $this->visitor->setMaxDepth(0); // 0 nested levels
        $this->assertFalse($this->visitor->isTraverseChildren());
    }

    function testMaxDepthExcludesLevels()
    {
        $parent = new T_Test_CompositeStub('parent');
        $child1 = new T_Test_CompositeStub('child1');
        $childchild1 = new T_Test_CompositeStub('childchild1');
        $childchild2 = new T_Test_CompositeStub('childchild2');
        $child2 = new T_Test_CompositeStub('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);
        $child1->addChild($childchild1);
        $child1->addChild($childchild2);

        ob_start();  // execute visitor
        $this->visitor->setMaxDepth(1);
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   'parent'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li>child1</li>'.EOL.
                   // ... missing levels ...
                   $this->indent.'<li>child2</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testExcludeNoEffect()
    {
        $this->visitor->setExcludeLevel(array(5,7,9));
        $this->testVisitCompositeWithTwoSubTreeLayers();
    }

    function testExcludeLastLevel()
    {
        $parent = new T_Test_CompositeStub('parent');
        $child1 = new T_Test_CompositeStub('child1');
        $childchild1 = new T_Test_CompositeStub('childchild1');
        $childchild2 = new T_Test_CompositeStub('childchild2');
        $child2 = new T_Test_CompositeStub('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);
        $child1->addChild($childchild1);
        $child1->addChild($childchild2);

        ob_start();  // execute visitor
        $this->visitor->setExcludeLevel(array(5,2));
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   'parent'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li>child1'.EOL.
                   // ... missing levels ...
                   $this->indent.'</li>'.EOL.
                   $this->indent.'<li>child2</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testExcludeFirstLevel()
    {
        $parent = new T_Test_CompositeStub('parent');
        $child1 = new T_Test_CompositeStub('child1');
        $child2 = new T_Test_CompositeStub('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $this->visitor->setExcludeLevel(array(0));
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<ul>'.EOL.
                   $this->indent.'<li>child1</li>'.EOL.
                   $this->indent.'<li>child2</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testExcludeMidLevel()
    {
        $parent = new T_Test_CompositeStub('parent');
        $child1 = new T_Test_CompositeStub('child1');
        $childchild1 = new T_Test_CompositeStub('childchild1');
        $childchild2 = new T_Test_CompositeStub('childchild2');
        $child2 = new T_Test_CompositeStub('child2');

        $parent->addChild($child1);
        $parent->addChild($child2);
        $child1->addChild($childchild1);
        $child1->addChild($childchild2);

        ob_start();  // execute visitor
        $this->visitor->setExcludeLevel(array(1));
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   'parent'.EOL.
                   $this->indent.'<ul>'.EOL.
                   $this->indent.$this->indent.'<li>childchild1</li>'.EOL.
                   $this->indent.$this->indent.'<li>childchild2</li>'.EOL.
                   $this->indent.'</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testResetDefaultsClearsMaxDepthAndExcludeLevels()
    {
        $expected = clone $this->visitor;
        $this->visitor->setMaxDepth(2);
        $this->visitor->setExcludeLevel(array(1,2,3));
        $this->visitor->resetDefaults();
        $this->assertEquals($expected,$this->visitor);
    }

    function testSetMaxDepthHasAFluentInterface()
    {
        $test = $this->visitor->setMaxDepth(1);
        $this->assertSame($test,$this->visitor);
    }

    function testSetExcludeLevelHasAFluentInterface()
    {
        $test = $this->visitor->setExcludeLevel(array(0));
        $this->assertSame($test,$this->visitor);
    }

    function testResetDefaultHasAFluentInterface()
    {
        $test = $this->visitor->resetDefaults();
        $this->assertSame($test,$this->visitor);
    }

}
