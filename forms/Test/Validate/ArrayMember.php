<?php
/**
 * Unit test cases for the T_Validate_ArrayMember class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ArrayMember test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ArrayMember extends T_Test_Filter_SkeletonHarness
{

    /**
     * Test has no effect if value is in options array.
     */
    function testFilterHasNoEffectIfValueInOptionsArray()
    {
        $filter = new T_Validate_ArrayMember(array(23,45,67));
        $this->assertSame($filter->transform(45),45);
    }

    /**
     * Test filter throws exception if NOT in options array.
     */
    function testThrowsExceptionIfNotInOptionsArray()
    {
        $filter = new T_Validate_ArrayMember(array(23,45,67));
        try {
            $filter->transform(32);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test that the comparison is not strict.
     */
    function testArrayMemberComparisonIsNotStrict()
    {
        $filter = new T_Validate_ArrayMember(array(23,45,67));
        $this->assertSame($filter->transform('45'),45);
    }


    /**
     * Test option array can be changed.
     */
    function testOptionsArrayCanBeChanged()
    {
        $filter = new T_Validate_ArrayMember(array(23,45,67));
        $filter->setOptions(array(1,2,3));
        $this->assertSame($filter->transform(2),2);
        try {
            $filter->transform(45);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test can pipe prior filter through.
     */
    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix('pipe');
        $filter = new T_Validate_ArrayMember(array(23,'firstpipe',67),$pipe);
        $this->assertSame($filter->transform('first'),'firstpipe');
    }

}