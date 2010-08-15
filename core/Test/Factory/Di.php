<?php
interface T_Example_DI_A_Interface { }
class T_Example_DI_A implements T_Example_DI_A_Interface { }
class T_Example_DI_A_Child extends T_Example_DI_A implements T_Decorated
{
    function isA($class) { return $this instanceof $class; }
    function getClass() { return get_class($this); }
}
class T_Example_DI_B
{
    function __construct(T_Example_DI_A $a) { $this->a = $a; }
}

class T_Test_Factory_Di extends T_Unit_Case
{

    /**
     * Gets a test DI container.
     *
     * @return T_Factory_Di
     */
    function getContainer()
    {
        return new T_Factory_Di();
    }

    function testFactoryRegistersItselfToUseAsFactoryDependency()
    {
        $factory= $this->getContainer();
        $this->assertSame($factory,$factory->like('T_Factory'));
    }

    function testCanUseContainerToRetrieveCreatedGlobals()
    {
        $test = new T_Cage_Array(array(1,2,3));
        $di = $this->getContainer();
        $di->willUse($test,'TEST');
        $this->assertSame($test,$di->like('TEST'));
        $this->assertSame($test,$di->like('TEST'));
    }

    function testCanCreateNamedClassWithNoConstructorArgs()
    {
        $di = $this->getContainer();
        $this->assertTrue($di->like('T_Example_DI_A') instanceof T_Example_DI_A);
        $this->assertNotSame($di->like('T_Example_DI_A'),$di->like('T_Example_DI_A'));
    }

    function testCanCreateChildClassFromParentName()
    {
        $di = $this->getContainer();
        $di->willUse('T_Example_DI_A_Child');
        $this->assertTrue($di->like('T_Example_DI_A') instanceof T_Example_DI_A_Child);
    }

    function testCanCreateClassFromInterfaceName()
    {
        $di = $this->getContainer();
        $di->willUse('T_Example_DI_A');
        $this->assertTrue($di->like('T_Example_DI_A_Interface') instanceof T_Example_DI_A);
    }

    function testCanCreateClassFromParentInterfaceName()
    {
        $di = $this->getContainer();
        $di->willUse('T_Example_DI_A_Child');
        $this->assertTrue($di->like('T_Example_DI_A_Interface') instanceof T_Example_DI_A_Child);
    }

    function testCanCreateClassWithArgsAndDefault()
    {
        $di = $this->getContainer();
        $test = $di->like('T_Url',array('scheme'=>'http','host'=>'example.com',
                                       'path'=>array('path'),'parameters'=>array()) );
        $this->assertEquals($test,new T_Url('http','example.com',array('path'),array()));
    }

    function testErrorWhenTryToCreateClassWithMissingArgInfo()
    {
        $di = $this->getContainer();
        try {
            $di->like('T_Url');
            $this->fail();
        } catch (RuntimeException $e) { }
    }

    function testTypeHintedArgsAreAutomaticallyPopulated()
    {
        $di = $this->getContainer();
        $this->assertEquals($di->like('T_Example_DI_B'),new T_Example_DI_B(new T_Example_DI_A()));
    }

    function testOptionalTypeHintedArgsAreNotAutomaticallyPopulated()
    {
        $di = $this->getContainer();
        $test = $di->like('T_Filter_RepeatableHash');
        $this->assertEquals($test,new T_Filter_RepeatableHash());
    }

    function testSettingToUseDecoratedObjectTreatsDecoratorAsTransparent()
    {
        $di = $this->getContainer();
        $decorated = new T_Decorator(new T_Example_DI_A_Child);
        $di->willUse($decorated);
        $this->assertSame($di->like('T_Example_DI_A_Child'),$decorated);
        $this->assertSame($di->like('T_Example_DI_A'),$decorated);
    }

}
