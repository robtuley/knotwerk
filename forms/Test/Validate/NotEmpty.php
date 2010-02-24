<?php
/**
 * Unit test cases for the T_Validate_NotEmpty class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_NotEmpty unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_NotEmpty extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnArrayWithMembers()
    {
        $filter = new T_Validate_NotEmpty();
        $this->assertSame($filter->transform(array('value')),array('value'));
    }

    function testFilterHasNoEffectOnStringWithSomeLength()
    {
        $filter = new T_Validate_NotEmpty();
        $this->assertSame($filter->transform('value'),'value');
    }

    function testFilterHasNoEffectOnInteger()
    {
        $filter = new T_Validate_NotEmpty();
        $this->assertSame($filter->transform(45),45);
    }

    function testFilterHasNoEffectOnZero()
    {
        $filter = new T_Validate_NotEmpty();
        $this->assertSame($filter->transform(0),0);
    }

    function testFilterHasNoEffectOnObject()
    {
        $filter = new T_Validate_NotEmpty();
        $value  = new T_Cage_Scalar('value');
        $this->assertSame($filter->transform($value),$value);
    }

    function testFilterFailsWhenEmptyArray()
    {
        $filter = new T_Validate_NotEmpty();
        try {
            $filter->transform(array());
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFilterFailsWhenEmptyString()
    {
        $filter = new T_Validate_NotEmpty();
        try {
            $filter->transform('');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix('pipe');
        $filter = new T_Validate_NotEmpty($pipe);
        $this->assertSame($filter->transform('first'),'firstpipe');
    }

}