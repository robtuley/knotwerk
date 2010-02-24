<?php
/**
 * Unit test cases for the T_Validate_WithinDateRange class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_WithinDateRange unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_WithinDateRange extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnDateWithinRange()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,300);
        $this->assertSame($filter->transform(200),200);
    }

    function testFilterIncludesLowerLimit()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,1000);
        $this->assertSame($filter->transform(100),100);
    }

    function testFilterIncludesUpperLimit()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,1000);
        $this->assertSame($filter->transform(1000),1000);
    }

    function testFilterFailsIfDateOutOfRange()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,1000);
        $invalid = array(99,-5,1001,2000);
        foreach ($invalid as $term) {
            try {
                $filter->transform($term);
                $this->fail("Failed on term $term");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testFilterFailureMessageFormatsDateWhenUnderLimit()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,1000);
        try {
            $filter->transform(10);
            $this->fail();
        } catch (T_Exception_Filter $e) {
            $this->assertContains(date('d-m-Y',100),$e->getMessage());
        }
    }

    function testFilterFailureMessageFormatsDateWhenOverLimit()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,1000);
        try {
            $filter->transform(1001);
            $this->fail();
        } catch (T_Exception_Filter $e) {
            $this->assertContains(date('d-m-Y',1000),$e->getMessage());
        }
    }

    function testNullLowerLimitRepresentsNoLowerLimit()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',null,1000);
        $this->assertSame($filter->transform(10),10);
        $this->assertSame($filter->transform(999),999);
        try {
            $filter->transform(1001);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testNullUpperLimitRepresentsNoUpperLimit()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',100,null);
        $this->assertSame($filter->transform(100),100);
        $this->assertSame($filter->transform(5000),5000);
        try {
            $filter->transform(99);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testNullBothLimitsRepresentsNoLimits()
    {
        $filter = new T_Validate_WithinDateRange('d-m-Y',null,null);
        $this->assertSame($filter->transform(5),5);
        $this->assertSame($filter->transform(1000),1000);
        $this->assertSame($filter->transform(10000),10000);
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Validate_UnixDate('d|m|y');
        $filter = new T_Validate_WithinDateRange('d-m-Y',24*60*60,2*24*60*60,$pipe);
        $this->assertSame($filter->transform('02/01/1970'),$pipe->transform('02/01/1970'));
    }

}