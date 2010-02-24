<?php
/**
 * Unit test cases for the T_Validate_Salt class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Salt test cases.
 *
 * @package coreTests
 */
class T_Test_Validate_Salt extends T_Test_Filter_SkeletonHarness
{

    function testDoesNotAffectNormalSaltStringsOfAnyLength()
    {
        $filter = new T_Validate_Salt();
        $salt = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $this->assertSame($filter->transform($salt),$salt);
        $this->assertSame($filter->transform('abcdef'),'abcdef');
    }

    function testDoesNotAllowNonAlphanumericChars()
    {
        $filter = new T_Validate_Salt();
        try {
            $filter->transform('ab&cdef');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testDoesNotAffectSaltOfFixedLength()
    {
        $filter = new T_Validate_Salt(10);
        $this->assertSame($filter->transform('0123456789'),'0123456789');
        $this->assertSame($filter->transform('abcdefghij'),'abcdefghij');
    }

    function testFailsWhenNotCorrectLenWhenSpecified()
    {
        $filter = new T_Validate_Salt(10);
        try {
            $filter->transform('short');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
        try {
            $filter->transform('longlonglong');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFailsWhenCorrectLenButContainsNonAlphanumericChars()
    {
        $filter = new T_Validate_Salt(10);
        try {
            $filter->transform('ab cdef123');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Validate_Salt(3,'mb_trim');
        $this->assertSame($filter->transform(' abc '),'abc');
    }

}