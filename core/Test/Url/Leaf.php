<?php
class T_Test_Url_Leaf extends T_Unit_Case
{

    protected $url;

    function setUp()
    {
        $this->url = new T_Url_Leaf('title','http','example.com');
    }

    function testGetComposite()
    {
        $this->assertTrue(is_null($this->url->getComposite()));
    }

    function testNotActiveByDefault()
    {
        $this->assertFalse($this->url->isActive());
    }

    function testSetActiveChangesActiveState()
    {
        $this->assertFalse($this->url->isActive());
        $this->assertSame($this->url,$this->url->setActive(),'fluent');
        $this->assertTrue($this->url->isActive());
    }

    function testActiveStatusPropagatedToParent()
    {
        $parent = new T_Url_Collection('diff','https','example.com');
        $parent->addChild($this->url);
        $this->url->setActive();
        $this->assertTrue($parent->isActive());
    }

    function testLeafVisitsItselfOnly()
    {
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$this->url);
        $this->assertTrue(count($visited['object'])==1);
    }

    function testLeafCallsCorrectVisitorMethod()
    {
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['method'][0],'visitUrlLeaf');
    }

    function testLeafVisitedAtCorrectDepth()
    {
        $visitor = new T_Test_VisitorStub();
        $this->url->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['depth'][0],0);
    }

}