<?php
/**
 * Unit test cases for the T_Filter_Xhtml class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_Xhtml test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_Xhtml extends T_Test_Filter_SkeletonHarness
{

    /**
     * Test does not affect non special characters.
     */
    function testDoesNotAffectNonSpecialCharacters()
    {
        $filter = new T_Filter_Xhtml();
        $this->assertSame($filter->transform(' MiXeD cAsE '),' MiXeD cAsE ');
    }

    /**
     * Test escapes single and double quotes.
     */
    function testEscapesDoubleNotSingleQuotes()
    {
        $filter = new T_Filter_Xhtml();
        $test = 'A\'and"';
        $expected = 'A\'and&quot;';
        $this->assertSame($filter->transform($test),$expected);
    }

    /**
     * Test escapes special characters.
     */
    function testEscapesSpecialCharacters()
    {
        $filter = new T_Filter_Xhtml();
        $this->assertSame($filter->transform('High & low'),'High &amp; low');
    }

    /**
     * Test can pipe prior filter through.
     */
    function testCanApplyPriorFilter()
    {
        $filter = new T_Filter_Xhtml('mb_trim');
        $this->assertSame($filter->transform(' MiXeD cAsE '),'MiXeD cAsE');
    }

}