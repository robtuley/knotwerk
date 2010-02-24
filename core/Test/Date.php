<?php
/**
 * Unit test cases for the T_Date class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Date unit test cases.
 *
 * @package coreTests
 */
class T_Test_Date extends T_Unit_Case
{

    function testDayMonthAndYearSetInConstructor()
    {
        $date = new T_Date(17,2,1910);
        $this->assertSame($date->getDay(),17);
        $this->assertSame($date->getMonth(),2);
        $this->assertSame($date->getYear(),1910);
    }

    function testDayCanBeChanged()
    {
        $date = new T_Date(17,2,1910);
        $date->setDay(21);
        $this->assertSame($date->getDay(),21);
    }

    function testSetDayHasAFluentInterface()
    {
        $date = new T_Date(17,2,1910);
        $test = $date->setDay(21);
        $this->assertSame($date,$test);
    }

    function testLeadingZerosAreStrippedFromDay()
    {
        $date = new T_Date('05',2,1910);
        $this->assertSame($date->getDay(),5);
        $date->setDay('09');
        $this->assertSame($date->getDay(),9);
    }

    function testDayCanBeNull()
    {
        $date = new T_Date(null,2,1980);
        $this->assertSame($date->getDay(),null);
    }

    function testMonthCanBeChanged()
    {
        $date = new T_Date(17,2,1910);
        $date->setMonth(5);
        $this->assertSame($date->getMonth(),5);
    }

    function testSetMonthHasAFluentInterface()
    {
        $date = new T_Date(17,2,1910);
        $test = $date->setMonth(5);
        $this->assertSame($date,$test);
    }

    function testLeadingZerosAreStrippedFromMonth()
    {
        $date = new T_Date(17,'02',1910);
        $this->assertSame($date->getMonth(),2);
        $date->setMonth('09');
        $this->assertSame($date->getMonth(),9);
    }

    function testMonthCanBeNull()
    {
        $date = new T_Date(17,null,1980);
        $this->assertSame($date->getMonth(),null);
    }

    function testYearCanBeChanged()
    {
        $date = new T_Date(17,2,1910);
        $date->setYear(1987);
        $this->assertSame($date->getYear(),1987);
    }

    function testSetYearHasAFluentInterface()
    {
        $date = new T_Date(17,2,1910);
        $test = $date->setYear(1987);
        $this->assertSame($date,$test);
    }

    function testYearCanBeNull()
    {
        $date = new T_Date(17,2,null);
        $this->assertSame($date->getYear(),null);
    }

    function testDayCanBeFilteredOnRetrieval()
    {
        $date = new T_Date(17,2,1910);
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($date->getDay($f),$f->transform(17));
    }

    function testMonthCanBeFilteredOnRetrieval()
    {
        $date = new T_Date(17,2,1910);
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($date->getMonth($f),$f->transform(2));
    }

    function testYearCanBeFilteredOnRetrieval()
    {
        $date = new T_Date(17,2,1910);
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($date->getYear($f),$f->transform(1910));
    }

    function testDayFormats()
    {
        $date = new T_Date(5,2,1910);
        $this->assertSame($date->asFormat('d'),'05');
        $this->assertSame($date->asFormat('j'),'5');
    }

    function testMonthFormats()
    {
        $date = new T_Date(5,2,1910);
        $this->assertSame($date->asFormat('m'),'02');
        $this->assertSame($date->asFormat('n'),'2');
    }

    function testYearFormats()
    {
        $date = new T_Date(5,2,1910);
        $this->assertSame($date->asFormat('Y'),'1910');
        $this->assertSame($date->asFormat('y'),'10');
    }

    function testShortYearIsProducesSameForLongAndShortYear()
    {
        $date = new T_Date(5,2,10);
        $this->assertSame($date->asFormat('Y'),'10');
        $this->assertSame($date->asFormat('y'),'10');
    }

    function testCombinedFormats()
    {
        $date = new T_Date(5,2,1910);
        $this->assertSame($date->asFormat('d-m-Y'),'05-02-1910');
        $this->assertSame($date->asFormat('j-n-y'),'5-2-10');
    }

    function testFormatStringCanBeFiltered()
    {
        $date = new T_Date(5,2,1910);
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($date->asFormat('d-m-Y',$f),$f->transform('05-02-1910'));
    }

}