<?php
/**
 * Unit test cases for the T_Validate_Date class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Date test cases.
 *
 * @package coreTests
 */
class T_Test_Validate_Date extends T_Test_Filter_SkeletonHarness
{

    /**
     * Assert that the string gets converted to the appropriate timestamp.
     *
     * @param string $str  string date
     * @param int $day  day
     * @param int $month month
     * @param int $year  year
     */
    protected function assertIsConverted($str,$day,$month,$year,$fmt)
    {
        $filter = new T_Validate_Date($fmt);
        $test = $filter->transform($str);
        $this->assertSame($test->getDay(),$day);
        $this->assertSame($test->getMonth(),$month);
        $this->assertSame($test->getYear(),$year);
    }

    function testFailureWithInvalidDate()
    {
        $filter = new T_Validate_Date('d|m|y');
        try {
            $filter->transform('invalidDate');
            $this->fail('Accepts an invalid date');
        } catch (T_Exception_Filter $e) { }
    }

    function testFailureWithEmptyString()
    {
        $filter = new T_Validate_Date('d|m|y');
        try {
            $filter->transform('');
            $this->fail('Accepts an empty string');
        } catch (T_Exception_Filter $e) { }
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Validate_Date('d|m|y',new T_Filter_DateStr('d/m/Y'));
        $time = 1088994102;
        $test = $filter->transform($time);
        $expect = new T_Date(date('j',$time),date('n',$time),date('Y',$time));
        $this->assertEquals($expect,$test);
    }

    function testExceptionOnInvalidDateFormat()
    {
        $invalid = array('','d|s|y','d|mm|y','dmy','d| m|y',' d|m|y');
        foreach ($invalid as $fmt) {
            try {
                $filter = new T_Validate_Date($fmt);
                $this->fail();
            } catch (InvalidArgumentException $e) { }
        }
    }

    function testDateThatMatchesFormat()
    {
        $this->assertIsConverted('17/2/2002',17,2,2002,'d|m|y');
        $this->assertIsConverted('2/17/2002',17,2,2002,'m|d|y');
        $this->assertIsConverted('2002/2/17',17,2,2002,'y|m|d');
    }

    function testDateWithLeadingZeros()
    {
        $this->assertIsConverted('17/02/2002',17,2,2002,'d|m|y');
        $this->assertIsConverted('03/06/2002',3,6,2002,'d|m|y');
    }

    function testDateWithTwoDigitYear()
    {
        $this->assertIsConverted('17/02/02',17,2,2002,'d|m|y');
        $this->assertIsConverted('17/02/86',17,2,1986,'d|m|y');
        $this->assertIsConverted('17/02/00',17,2,2000,'d|m|y');
    }

    function testDateOutsideUnixRangeIsOk()
    {
        $this->assertIsConverted('31/12/1969',31,12,1969,'d|m|y');
        $this->assertIsConverted('2/3/2039',2,3,2039,'d|m|y');
    }

    function testDefaultPartsAreToday()
    {
        $today = getdate();
        $day = $today['mday'];
        $month = $today['mon'];
        $year = $today['year'];
        $this->assertIsConverted('17/2',17,2,$year,'d|m');
        $this->assertIsConverted('12/2002',$day,12,2002,'m|y');
        $this->assertIsConverted('2002/17',17,$month,2002,'y|d');
    }

    function testDelimiterCanBeSlashDotOrDash()
    {
        $this->assertIsConverted('17/2/2002',17,2,2002,'d|m|y');
        $this->assertIsConverted('17.2.2002',17,2,2002,'d|m|y');
        $this->assertIsConverted('17-2-2002',17,2,2002,'d|m|y');
        $this->assertIsConverted('17-2.2002',17,2,2002,'d|m|y');
    }

    function testDateThatDoesNotMatchFormat()
    {
        $invalid = array('d|m|y' => '17/2',
                         'd|m|y' => '12/2/2002/3',
                         'd|y' => '12/2/2002',
                         'y' => '2/3');
        foreach ($invalid as $fmt => $date) {
            try {
                $filter = new T_Validate_Date($fmt);
                $filter->transform($date);
                $this->fail();
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testDateThatMatchesFormatButIsInvalid()
    {
        $invalid = array('d|m|y' => '17/2/-123',
                         'd|m|y' => '123/2/2002',
                         'd|m|y' => '31/2/2003',
                         'd|m|y' => '15/13/2002',
                         'm|y' => '13/2002');
        foreach ($invalid as $fmt => $date) {
            try {
                $filter = new T_Validate_Date($fmt);
                $filter->transform($date);
                $this->fail("Date $date does not match format $fmt");
            } catch (T_Exception_Filter $e) { }
        }
    }

}