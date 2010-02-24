<?php
/**
 * Unit test cases for the T_Validate_HexHash class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * T_Validate_HexHash test cases.
 *
 * @package coreTests
 */
class T_Test_Validate_HexHash extends T_Test_Filter_SkeletonHarness
{

    function testNotEffectOnValidHash()
    {
        $filter = new T_Validate_HexHash();
        $hash = md5('hashthis');
        $this->assertSame($filter->transform($hash),$hash);
    }

    function testRejectsValueThatIsMoreThan32Chars()
    {
        $filter = new T_Validate_HexHash();
        $hash = md5('hashthis');
        try {
            $filter->transform($hash.'a');
            $this->fail();
        } catch (T_Exception_Filter $e) {}
    }

    function testRejectsValueThatIsLessThan32Chars()
    {
        $filter = new T_Validate_HexHash();
        $hash = md5('hashthis');
        try {
            $filter->transform(substr($hash,0,31));
            $this->fail();
        } catch (T_Exception_Filter $e) {}
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Validate_HexHash('mb_trim');
        $hash = md5('hashthis');
        $this->assertSame($filter->transform("  $hash "),$hash);
    }

}