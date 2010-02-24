<?php
/**
 * Unit test cases for the T_Filter_HumanTimePeriod class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_HumanTimePeriod test cases.
 *
 * @package coreTests
 */
class T_Test_Filter_HumanTimePeriod extends T_Test_Filter_SkeletonHarness
{

    function testZeroTime()
    {
        $filter = new T_Filter_HumanTimePeriod();
        $this->assertSame($filter->transform(0),'0 seconds');
    }

    function testLessThanAMinute()
    {
        $filter = new T_Filter_HumanTimePeriod();
        $this->assertSame($filter->transform(1),'1 second');
        $this->assertSame($filter->transform(13),'13 seconds');
        $this->assertSame($filter->transform(59),'59 seconds');
    }

    function testLessThanAnHour()
    {
        $filter = new T_Filter_HumanTimePeriod();
        $this->assertSame($filter->transform(60),'1 minute');
        $this->assertSame($filter->transform(13*60+12),'13 minutes');
        $this->assertSame($filter->transform(56*60+45),'57 minutes');
        $this->assertSame($filter->transform(59*60+29),'59 minutes');
    }

    function testLessThanADay()
    {
        $filter = new T_Filter_HumanTimePeriod();
        $this->assertSame($filter->transform(60*60),'1 hour');
        $this->assertSame($filter->transform(59*60+30),'1 hour');
        $this->assertSame($filter->transform(60*60+67),'1 hour 1 minute');
        $this->assertSame($filter->transform(60*60+27*60-10),'1 hour 27 minutes');
        $this->assertSame($filter->transform(3*60*60+45*60),'3 hours 45 minutes');
        $this->assertSame($filter->transform(3*60*60+1*60),'3 hours 1 minute');
        $this->assertSame($filter->transform(23*60*60+59*60+29),'23 hours 59 minutes');
    }

    function testMoreThanADay()
    {
        $filter = new T_Filter_HumanTimePeriod();
        $this->assertSame($filter->transform(23*60*60+59*60+30),'1 day');
        $this->assertSame($filter->transform(24*60*60+29*60+29),'1 day');
        $this->assertSame($filter->transform(24*60*60+1*60*60+13*60+1),'1 day 1 hour');
        $this->assertSame($filter->transform(24*60*60+3*60*60-13*60+1),'1 day 3 hours');
        $this->assertSame($filter->transform(5*24*60*60+0.25*60*60),'5 days');
        $this->assertSame($filter->transform(5*24*60*60+23*60*60),'5 days 23 hours');
        $this->assertSame($filter->transform(35*24*60*60-0.25*60*60),'35 days');
    }

}