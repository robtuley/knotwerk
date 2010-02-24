<?php
class T_Test_View_Composite extends T_Unit_Case
{
    protected $composite;

    function setUp()
    {
        $this->composite = new T_View_Composite();
    }

    protected function getViewLeaf()
    {
        return new T_Test_CompositeStub();
    }

    // tests

    function testIsChildrenFalseWithNoChildren()
    {
        $this->assertFalse($this->composite->isChildren());
    }

    function testAddSingleChild()
    {
        $this->composite->addChild($this->getViewLeaf());
        $this->assertTrue($this->composite->isChildren());
    }

    function testGetComposite()
    {
        $comp = $this->composite->getComposite();
        $this->assertSame($comp,$this->composite);
    }

    function testAddChildMethodHasAFluentInterface()
    {
        $child  = $this->getViewLeaf();
        $test = $this->composite->addChild($child);
        $this->assertSame($this->composite,$test);
    }

    function testRetrieveChildWithKeyword()
    {
        $child = $this->getViewLeaf();
        $this->composite->addChild($child,'test');
        $this->assertTrue($this->composite->isChildren());
        $this->assertSame($child,$this->composite->test);
    }

    function testRetrieveChildFailureWhenNotExist()
    {
        try {
            $test = $this->composite->notachild;
            $this->fail();
        } catch(InvalidArgumentException $e) {}
    }

    function testOutputsZeroLengthStringOnRenderWithNoChildren()
    {
        $this->assertSame($this->composite->__toString(),'');
    }

    function testOutputsSingleChildContentOnRender()
    {
        $child = new T_Test_CompositeStub('somecontent');
        $this->composite->addChild($child);
        $this->assertSame($this->composite->__toString(),$child->__toString());
    }

    function testOutputsChildContentOnRenderInOrderAdded()
    {
        $child1 = new T_Test_CompositeStub('first');
        $child2 = new T_Test_CompositeStub('second');
        $this->composite->addChild($child1);
        $this->composite->addChild($child2);
        $expected = $child1->__toString().$child2->__toString();
        $this->assertSame($this->composite->__toString(),$expected);
    }

    function testOutputsZeroToBufferWhenNoChildren()
    {
        ob_start();
        $test = $this->composite->toBuffer();
        $this->assertSame($test,$this->composite,'fluent interface');
        $content = ob_get_clean();
        $this->assertSame(strlen($content),0);
    }

    function testOutputsSingleChildContentToBuffer()
    {
        $child = new T_Test_CompositeStub('somecontent');
        $this->composite->addChild($child);
        ob_start();
        $this->composite->toBuffer();
        $content = ob_get_clean();
        $this->assertSame($content,$child->__toString());
    }

    function testOutputsChildContentToBufferInOrderAdded()
    {
        $child1 = new T_Test_CompositeStub('first');
        $child2 = new T_Test_CompositeStub('second');
        $this->composite->addChild($child1);
        $this->composite->addChild($child2);
        $expected = $child1->__toString().$child2->__toString();
        ob_start();
        $this->composite->toBuffer();
        $content = ob_get_clean();
        $this->assertSame($expected,$content);
    }

}
