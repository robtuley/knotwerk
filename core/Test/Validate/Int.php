<?php
/**
 * Unit test cases for the T_Validate_Int class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Int test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Validate_Int extends T_Test_Filter_SkeletonHarness
{

    function testDoesNotAffectInteger()
    {
        $filter = new T_Validate_Int();
        $this->assertSame($filter->transform(21),21);
    }

    function testStringCastAsInteger()
    {
        $filter = new T_Validate_Int();
        $this->assertSame($filter->transform('21'),21);
    }

    function testRejectsFloatingNumber()
    {
        $filter = new T_Validate_Int();
        try {
            $filter->transform(3.2);
            $this->fail();
        } catch (T_Exception_Filter $e) {}
    }

    function testRejectsString()
    {
        $filter = new T_Validate_Int();
        try {
            $filter->transform('text');
            $this->fail();
        } catch (T_Exception_Filter $e) {}
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Validate_Int('mb_trim');
        $this->assertSame($filter->transform(' 21 '),21);
    }

}