<?php
/**
 * Unit test cases for the T_Decorator class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Decorator unit test cases.
 *
 * @package coreTests
 */
class T_Test_Decorator extends T_Unit_Case
{

    function testDecoratorImplementsTransparentInterface()
    {
        $decorator = new T_Decorator(new T_Date(17,2,1910));
        $this->assertTrue($decorator instanceof T_Transparent);
    }

    function testLookUnderRetrievesTarget()
    {
        $decorator = new T_Decorator($date=new T_Date(17,2,1910));
        $this->assertSame($date,$decorator->lookUnder());
    }

    function testLookUnderRetrievesTargetEvenWhenNestedUnderMultipleDecorators()
    {
        $decorator = new T_Decorator(new T_Decorator($date=new T_Date(17,2,1910)));
        $this->assertSame($date,$decorator->lookUnder());
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
