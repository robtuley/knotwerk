<?php
class T_Test_Url_Collection extends T_Unit_Case
{

    protected $url;

    function setUp()
    {
        $this->url = new T_Url_Collection('title','http','example.com');
    }

    protected function getUrlComposite()
    {
        return new T_Url_Collection('diff','https','example.com');
    }

    function testIsChildrenFalseWithNoChildren()
    {
        $this->assertFalse($this->url->isChildren());
    }

    function testAddChildMethodHasAFluentInterface()
    {
        $test = $this->url->addChild($this->getUrlComposite());
        $this->assertSame($this->url,$test);
    }

    function testAddSingleChild()
    {
        $this->url->addChild($this->getUrlComposite());
        $this->assertTrue($this->url->isChildren());
    }

    function testGetComposite()
    {
        $comp = $this->url->getComposite();
        $this->assertSame($comp,$this->url);
    }

    function testNotActiveByDefault()
    {
        $this->assertFalse($this->url->isActive());
    }

    function testSetActiveChangesActiveState()
    {
        $this->assertFalse($this->url->isActive());
        $this->url->setActive();
        $this->assertTrue($this->url->isActive());
    }

    function testSetLeafActive()
    {
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $this->url->addChild($child1);
        $this->url->addChild($child2);
        $child1->setActive();
        $this->assertTrue($child1->isActive());
        $this->assertFalse($child2->isActive());
        $this->assertTrue($this->url->isActive());
    }

    function testAddActiveChild()
    {
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $child1->setActive();
        $this->url->addChild($child1);
        $this->url->addChild($child2);
        $this->assertTrue($child1->isActive());
        $this->assertFalse($child2->isActive());
        $this->assertTrue($this->url->isActive());
    }

    function testRetrieveChildWithKeyword()
    {
        $child = $this->getUrlComposite();
        $this->url->addChild($child,'test');
        $this->assertTrue($this->url->isChildren());
        $this->assertSame($child,$this->url->test);
    }

    function testCanTestWhetherChildIsset()
    {
        $child = $this->getUrlComposite();
        $this->url->addChild($child,'test');
        $this->assertTrue(isset($this->url->test));
        $this->assertFalse(isset($this->url->notset));
    }

    function testRetrieveChildFailureWhenNotExist()
    {
        try {
            $test = $this->url->notachild;
            $this->fail();
        } catch(InvalidArgumentException $e) {}
    }

    function testCompositeVisitsItselfOnlyWithNoChildren()
    {
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$this->url);
        $this->assertTrue(count($visited['object'])==1);
    }

    function testCompositeCallsCorrectVisitorMethodWithNoChildren()
    {
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['method'][0],'visitUrlCollection');
    }

    function testCompositeVisitedAtCorrectDepthWithNoChildren()
    {
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['depth'][0],0);
    }

    function testCompositeVisitsItselfAndChildrenWhenTraverseOn()
    {
        // add children
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $this->url->addChild($child1);
        $this->url->addChild($child2);
        // visit parent
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        // check objects visited
        $this->assertTrue(count($visited['object'])==3);
        $this->assertSame($visited['object'][0],$this->url);
        $this->assertSame($visited['object'][1],$child1);
        $this->assertSame($visited['object'][2],$child2);
        // check depths visited.
        $this->assertSame($visited['depth'][0],0);
        $this->assertSame($visited['depth'][1],1);
        $this->assertSame($visited['depth'][2],1);
    }

    function testCompositeVisitsItselfNotChildrenWhenTraverseOff()
    {
        // add children
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $this->url->addChild($child1);
        $this->url->addChild($child2);
        // visit parent
        $visitor = new T_Test_VisitorStub();
        $visitor->setTraverseChildren(false);
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        // check objects visited
        $this->assertTrue(count($visited['object'])==1);
        $this->assertSame($visited['object'][0],$this->url);
        $this->assertSame($visited['depth'][0],0);
    }

    function testIterationWithNoChildren()
    {
        foreach ($this->url as $child) {
            $this->fail();
        }
    }

    function testIterationWithSingleChild()
    {
        $child = $this->getUrlComposite();
        $this->url->addChild($child);
        $i=0;
        foreach ($this->url as $test) {
            $i++;
            $this->assertSame($child,$test);
        }
        $this->assertSame($i,1);
    }

    function testIterationWithExplicitChildKey()
    {
        $child = $this->getUrlComposite();
        $this->url->addChild($child,'akey');
        $i=0;
        foreach ($this->url as $key => $test) {
            $i++;
            $this->assertSame($child,$test);
            $this->assertSame('akey',$key);
        }
        $this->assertSame($i,1);
    }

    function testIterationWithMultipleChildren()
    {
        // add children
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $this->url->addChild($child1);
        $this->url->addChild($child2,'akey');
        $expected = array(0=>$child1,'akey'=>$child2);
        $test = array();
        foreach ($this->url as $key => $value) {
            $test[$key] = $value;
        }
        $this->assertSame($test,$expected);
    }

    function testGetActiveChildReturnsFalseWithNoChildren()
    {
        $this->assertFalse($this->url->getActiveChild());
    }

    function testGetActiveChildReturnsFalseWithNoActiveChildren()
    {
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $this->url->addChild($child1,'child1');
        $this->url->addChild($child2,'child2');
        $this->assertFalse($this->url->getActiveChild());
    }

    function testGetActiveChildReturnsActiveChildren()
    {
        $child1 = $this->getUrlComposite();
        $child2 = $this->getUrlComposite();
        $this->url->addChild($child1,'child1');
        $this->url->addChild($child2,'child2');
        $this->url->child2->setActive();
        $this->assertSame($child2,$this->url->getActiveChild());
    }

}