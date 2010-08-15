<?php
class T_Test_Decorator extends T_Unit_Case
{

    function testDecoratorImplementsTransparentInterface()
    {
        $decorator = new T_Decorator(new T_Date(17,2,1910));
        $this->assertTrue($decorator instanceof T_Decorated);
    }

    function testgetClassAndIsAUsedToQueryObject()
    {
        $decorator = new T_Decorator($date=new T_Date(17,2,1910));
        $this->assertSame(get_class($date),$decorator->getClass());
        $this->assertTrue($decorator->isA('T_Date'));
        $this->assertTrue($decorator->isA('T_Decorator'));
        $this->assertFalse($decorator->isA('T_Filter'));
    }

    function testQueryMethodsOkWhenNestedUnderMultipleDecorators()
    {
        $decorator = new T_Decorator(new T_Decorator($date=new T_Date(17,2,1910)));
        $this->assertSame(get_class($date),$decorator->getClass());
        $this->assertTrue($decorator->isA('T_Date'));
        $this->assertTrue($decorator->isA('T_Decorator'));
        $this->assertFalse($decorator->isA('T_Filter'));
    }

    function testDecoratorPassesThroughMethodWithNoArgs()
    {
        $decorator = new T_Decorator(new T_Date(17,2,1910));
        $this->assertSame(17,$decorator->getDay());
    }

    function testDecoratorPassesThroughMethodWithArg()
    {
        $decorator = new T_Decorator(new T_Date(17,2,1910));
        $decorator->setDay(28);
        $this->assertSame(28,$decorator->getDay());
    }

    function testDecoratorReturnsFluentReturnsWithDecoratorStillWrapped()
    {
        $decorator = new T_Decorator(new T_Date(17,2,1910));
        $this->assertSame($decorator,$decorator->setDay(28));
    }

    function testDecoratorReturnsFluentReturnsWithDecoratorStillWrappedWhenMultipleDecorators()
    {
        $decorator = new T_Decorator(new T_Decorator(new T_Date(17,2,1910)));
        $this->assertSame($decorator,$decorator->setDay(28));
    }

}
