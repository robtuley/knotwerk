<?php
/**
 * Unit test cases for the T_Validate_WithinNumericRange class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_WithinNumericRange unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_WithinNumericRange extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnIntegerWithinRange()
    {
        $filter = new T_Validate_WithinNumericRange(1,10);
        $this->assertSame($filter->transform(5),5);
    }

    function testFilterHasNoEffectOnFloatWithinRange()
    {
        $filter = new T_Validate_WithinNumericRange(0.5,0.7);
        $this->assertSimilarFloat($filter->transform(0.678),0.678);
    }

    function testFilterIncludesLowerLimit()
    {
        $filter = new T_Validate_WithinNumericRange(1,10);
        $this->assertSame($filter->transform(1),1);
    }

    function testFilterIncludesUpperLimit()
    {
        $filter = new T_Validate_WithinNumericRange(1,10);
        $this->assertSame($filter->transform(10),10);
    }

    function testFilterAcceptsStringNumeric()
    {
        $filter = new T_Validate_WithinNumericRange(0.5,0.7);
        $this->assertSimilarFloat($filter->transform('0.678'),'0.678');
    }

    function testRangeCanBeNegative()
    {
        $filter = new T_Validate_WithinNumericRange(-10,10);
        $this->assertSame($filter->transform(5),5);
        $this->assertSame($filter->transform(-5),-5);
        $this->assertSame($filter->transform('-5'),'-5');
        $this->assertSimilarFloat($filter->transform(-0.56),-0.56);
    }

    function testFilterFailsIfIntegerOutOfRange()
    {
        $filter = new T_Validate_WithinNumericRange(1,10);
        $invalid = array(0,-5,11,20);
        foreach ($invalid as $term) {
            try {
                $filter->transform($term);
                $this->fail("Failed on term $term");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testFilterFailsIfFloatOutOfRange()
    {
        $filter = new T_Validate_WithinNumericRange(0.5,0.9);
        $invalid = array(1.2,-0.6,0.4,0.91);
        foreach ($invalid as $term) {
            try {
                $filter->transform($term);
                $this->fail("Failed on term $term");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testNullLowerLimitRepresentsNoLowerLimit()
    {
        $filter = new T_Validate_WithinNumericRange(null,10);
        $this->assertSame($filter->transform(5),5);
        $this->assertSame($filter->transform(0),0);
        $this->assertSimilarFloat($filter->transform(-99.87),-99.87);
        $this->assertSame($filter->transform(-1000),-1000);
        try {
            $filter->transform(11);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testNullUpperLimitRepresentsNoUpperLimit()
    {
        $filter = new T_Validate_WithinNumericRange(1,null);
        $this->assertSame($filter->transform(5),5);
        $this->assertSame($filter->transform(11),11);
        $this->assertSimilarFloat($filter->transform(99.87),99.87);
        $this->assertSame($filter->transform(1000),1000);
        try {
            $filter->transform(0);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testNullBothLimitsRepresentsNoLimits()
    {
        $filter = new T_Validate_WithinNumericRange(null,null);
        $this->assertSame($filter->transform(5),5);
        $this->assertSame($filter->transform(1000),1000);
        $this->assertSame($filter->transform(-1000),-1000);
        $this->assertSimilarFloat($filter->transform(99.87),99.87);
        $this->assertSimilarFloat($filter->transform(-99.87),-99.87);
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix('1');
        $filter = new T_Validate_WithinNumericRange(1,20,$pipe);
        $this->assertSame($filter->transform('1'),'11');
           // 1 is suffixed with another 1 == 11
    }

}