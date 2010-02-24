<?php
/**
 * Unit test cases for the T_Filter_Filename class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_Filename test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_Filename extends T_Test_Filter_SkeletonHarness
{

    /**
     * Test accepts simple alphanumeric filename.
     */
    function testAcceptsAlphasNumericFilename()
    {
        $filter = new T_Filter_Filename();
        $expected = 'file13name.ext';
        $this->assertSame($filter->transform($expected),$expected);
    }

    /**
     * Test accepts filenames with underscores and dashes.
     */
    function testAcceptsUnderscoresAndDashes()
    {
        $filter = new T_Filter_Filename();
        $expected = 'fi_le-13name.e-xt_';
        $this->assertSame($filter->transform($expected),$expected);
    }

    /**
     * Test invalid character at start.
     */
    function testInvalidCharacterAtStart()
    {
        $filter = new T_Filter_Filename();
        $expected = '/file13name.ext';
        try {
            $filter->transform($expected);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test invalid character at end.
     */
    function testInvalidCharacterAtEnd()
    {
        $filter = new T_Filter_Filename();
        $expected = 'file13name.ext/';
        try {
            $filter->transform($expected);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test invalid character in middle.
     */
    function testInvalidCharacterInMiddle()
    {
        $filter = new T_Filter_Filename();
        $expected = 'file13/name.ext';
        try {
            $filter->transform($expected);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test can pipe prior filter through.
     */
    function testPipePriorFilter()
    {
        $prior = 'mb_strtolower';
        $filter = new T_Filter_Filename($prior);
        $fn = 'fiLe13nAmE.ext';
        $this->assertSame($filter->transform($fn),mb_strtolower($fn));
    }

}