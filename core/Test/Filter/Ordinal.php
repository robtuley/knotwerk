<?php
/**
 * Unit test cases for the T_Filter_Ordinal class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * T_Filter_Ordinal test cases.
 *
 * @package coreTests
 */
class T_Test_Filter_Ordinal extends T_Test_Filter_SkeletonHarness
{

    function testConvertsSingleDigitNumbers()
    {
        $filter = new T_Filter_Ordinal();
        $expect = array(1 => '1st',
                        2 => '2nd',
                        3 => '3rd',
                        4 => '4th',
                        5 => '5th',
                        6 => '6th',
                        7 => '7th',
                        8 => '8th',
                        9 => '9th');
        foreach ($expect as $key => $value) {
        	$this->assertSame($value,$filter->transform($key));
        }
    }

    function testConvertsTeenNumbers()
    {
        $filter = new T_Filter_Ordinal();
        $expect = array(10 => '10th',
                        11 => '11th',
                        12 => '12th',
                        13 => '13th',
                        14 => '14th',
                        15 => '15th',
                        16 => '16th',
                        17 => '17th',
                        18 => '18th',
                        19 => '19th');
        foreach ($expect as $key => $value) {
        	$this->assertSame($value,$filter->transform($key));
        }
    }

    function testConvertsLargeDoubleDigitNumbers()
    {
        $filter = new T_Filter_Ordinal();
        $this->assertSame('21st',$filter->transform(21));
        $this->assertSame('37th',$filter->transform(37));
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Filter_Ordinal(new T_Test_Filter_Suffix('2'));
        $this->assertSame('212th',$filter->transform(21));
    }

}