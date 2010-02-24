<?php
/**
 * Unit test cases for the T_Validate_UnixDate class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_UnixDate test cases.
 *
 * @package coreTests
 */
class T_Test_Validate_UnixDate extends T_Test_Filter_SkeletonHarness
{

    /**
     * Assert that the string gets converted to the appropriate timestamp.
     *
     * @param string $str  string date
     * @param int $timestamp  expected unix timestamp
     * @param int $tol  tolerance for the conversion
     */
    protected function assertIsConverted($str,$timestamp,$fmt=null)
    {
        $filter = new T_Validate_UnixDate($fmt);
        $test = $filter->transform($str);
        $this->assertSame($test,$timestamp);
    }

    /**
     * Get the expected timestamp (at 12:00 mid-day) for a date.
     *
     * @param int $day  day
     * @param int $month  month
     * @param int $year  year
     * @return int  UNIX timestamp
     */
    protected function getTimestamp($day,$month,$year)
    {
        return mktime(12,0,0,$month,$day,$year);
    }

    function testConvertsRfc822StringToTimestamp()
    {
        $this->assertIsConverted(date(DATE_RFC822,1088994102),1088994102);
    }

    function testConvertsGMTDateToLocalTimestamp()
    {
        $date = gmdate('D, d M Y H:i:s \G\M\T',1088994102);
        $this->assertIsConverted($date,1088994102);
    }

    function testFailureWithInvalidDate()
    {
        $filter = new T_Validate_UnixDate();
        try {
            $filter->transform('invalidDate');
            $this->fail('Accepts an invalid date');
        } catch (T_Exception_Filter $e) { }
    }

    function testFailureWithEmptyString()
    {
        $filter = new T_Validate_UnixDate();
        try {
            $filter->transform('');
            $this->fail('Accepts an empty string');
        } catch (T_Exception_Filter $e) { }
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Validate_UnixDate(null,new T_Filter_DateStr(DATE_RFC822));
        $test = $filter->transform(1088994102);
        $this->assertSame($test,1088994102);
    }

    function testExceptionOnInvalidDateFormat()
    {
        $invalid = array('','d|s|y','d|mm|y','dmy','d| m|y',' d|m|y');
        foreach ($invalid as $fmt) {
            try {
                $filter = new T_Validate_UnixDate($fmt);
                $this->fail();
            } catch (InvalidArgumentException $e) { }
        }
    }

    function testDateThatMatchesFormat()
    {
        $unix = $this->getTimestamp(17,2,2002);
        $this->assertIsConverted('17/2/2002',$unix,'d|m|y');
        $this->assertIsConverted('2/17/2002',$unix,'m|d|y');
        $this->assertIsConverted('2002/2/17',$unix,'y|m|d');
    }

    function testDateWithLeadingZeros()
    {
        $this->assertIsConverted('17/02/2002',$this->getTimestamp(17,2,2002),'d|m|y');
        $this->assertIsConverted('03/06/2002',$this->getTimestamp(3,6,2002),'d|m|y');
    }

    function testDateWithTwoDigitYear()
    {
        $this->assertIsConverted('17/02/02',$this->getTimestamp(17,2,2002),'d|m|y');
        $this->assertIsConverted('17/02/86',$this->getTimestamp(17,2,1986),'d|m|y');
        $this->assertIsConverted('17/02/00',$this->getTimestamp(17,2,2000),'d|m|y');
    }

    function testDateOutsideUnixRange()
    {
        $filter = new T_Validate_UnixDate('d|m|y');
        $dates = array('31/12/1969','02/06/69','2/3/2039','17/2/39');
        foreach ($dates as $date) {
            try {
                $filter->transform($date);
                $this->fail();
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testDefaultPartsAreToday()
    {
        $today = getdate();
        $day = $today['mday'];
        $month = $today['mon'];
        $year = $today['year'];
        $this->assertIsConverted('17/2',$this->getTimestamp(17,2,$year),'d|m');
        $this->assertIsConverted('12/2002',$this->getTimestamp($day,12,2002),'m|y');
        $this->assertIsConverted('2002/17',$this->getTimestamp(17,$month,2002),'y|d');
    }

    function testDelimiterCanBeSlashDotOrDash()
    {
        $unix = $this->getTimestamp(17,2,2002);
        $this->assertIsConverted('17/2/2002',$unix,'d|m|y');
        $this->assertIsConverted('17.2.2002',$unix,'d|m|y');
        $this->assertIsConverted('17-2-2002',$unix,'d|m|y');
        $this->assertIsConverted('17-2.2002',$unix,'d|m|y');
    }

    function testDateThatDoesNotMatchFormat()
    {
        $invalid = array('d|m|y' => '17/2',
                         'd|m|y' => '12/2/2002/3',
                         'd|y' => '12/2/2002',
                         'y' => '2/3');
        foreach ($invalid as $fmt => $date) {
            try {
                $filter = new T_Validate_UnixDate($fmt);
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
                         'm|y' => '13/2002',
                         'm|y' => '5/-123');
        foreach ($invalid as $fmt => $date) {
            try {
                $filter = new T_Validate_UnixDate($fmt);
                $filter->transform($date);
                $this->fail();
            } catch (T_Exception_Filter $e) { }
        }
    }

}