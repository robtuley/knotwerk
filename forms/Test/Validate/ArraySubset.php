<?php
/**
 * Unit test cases for the T_Validate_ArraySubset class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ArraySubset test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ArraySubset extends T_Test_Filter_SkeletonHarness
{

    /**
     * Test has no effect if value is an empty array.
     */
    function testFilterHasNoEffectIfValueIsEmptyArray()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        $this->assertSame($filter->transform(array()),array());
    }

    /**
     * Test has no effect if single value is valid.
     */
    function testFilterHasNoEffectIfSingleValueArrayIsValid()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        $this->assertSame($filter->transform(array(67)),array(67));
    }

    /**
     * Test has no effect if multiple value is valid.
     */
    function testFilterHasNoEffectIfMultipleValueArrayIsValid()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        $this->assertSame($filter->transform(array(67,23)),array(67,23));
    }

    /**
     * Test filter preserves the array order and key association.
     */
    function testFilterPreservesOrderAndKeys()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        $expect = array('akey'=>67,23,13=>67);
        $this->assertSame($filter->transform($expect),$expect);
    }

    /**
     * Test filter throws exception if single value NOT in options array.
     */
    function testThrowsExceptionIfSingleValueArrayNotInOptionsArray()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        try {
            $filter->transform(array(32));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test filter throws exception if multiple value NOT in options array.
     */
    function testFailsIfOneFromMultipleValueArrayNotInOptionsArray()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        try {
            $filter->transform(array(45,32,67));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test that the comparison is not strict.
     */
    function testArrayMemberComparisonIsNotStrict()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        $this->assertSame($filter->transform(array('45','23')),array(45,23));
    }


    /**
     * Test option array can be changed.
     */
    function testOptionsArrayCanBeChanged()
    {
        $filter = new T_Validate_ArraySubset(array(23,45,67));
        $filter->setOptions(array(1,2,3));
        $this->assertSame($filter->transform(array(2)),array(2));
        try {
            $filter->transform(array(45));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test can pipe prior filter through.
     */
    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_ArrayPrefix('pipe');
        $filter = new T_Validate_ArraySubset(array(23,'firstpipe',67),$pipe);
        $this->assertSame($filter->transform(array('first')),array('firstpipe'));
    }

}