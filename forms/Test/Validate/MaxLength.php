<?php
/**
 * Unit test cases for the T_Validate_MaxLength class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_MaxLength test cases.
 *
 * @package formTests
 */
class T_Test_Validate_MaxLength extends T_Test_Filter_SkeletonHarness
{

    /**
     * Test has no effect on a string if under max length.
     */
    function testFilterHasNoEffectIfStringUnderMaxLength()
    {
        $filter = new T_Validate_MaxLength(5);
        $this->assertSame($filter->transform('test'),'test');
    }

    /**
     * Test casts non-string to a string if under max length.
     */
    function testFilterCastsToAString()
    {
        $filter = new T_Validate_MaxLength(5);
        $this->assertSame($filter->transform(1234),'1234');
    }

    /**
     * Test has no effect on a string if equal to max length.
     */
    function testFilterHasNoEffectIfStringEqualToMaxLength()
    {
        $filter = new T_Validate_MaxLength(4);
        $this->assertSame($filter->transform('test'),'test');
    }


    /**
     * Test can change max length in filter.
     */
    function testCanChangeMaxLengthLimitOfFilter()
    {
        $filter = new T_Validate_MaxLength(4);
        $filter->setMaxLength(5);
        $this->assertSame($filter->transform('12345'),'12345');
    }

    /**
     * Test throws exception if string longer than max length.
     */
    function testFilterThrowsExceptionWhenStringIsTooLong()
    {
        $filter = new T_Validate_MaxLength(3);
        try {
            $filter->transform('test');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    /**
     * Test can pipe prior filter through.
     */
    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix('pipe');
        $filter = new T_Validate_MaxLength(10,$pipe);
        $this->assertSame($filter->transform('first'),'firstpipe');
    }

}