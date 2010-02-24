<?php
/**
 * Unit test cases for the T_Geo_Country class.
 *
 * @package geoTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Geo_Country test cases.
 *
 * @package geoTests
 */
class T_Test_Geo_Country extends T_Unit_Case
{

    function testIdSetInConstructor()
    {
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame(13,$ctry->getId());
    }

    function testCodeSetInConstructor()
    {
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame('GB',$ctry->getCode());
    }

    function testCodeSetInConstructorIsConvertedToUpperCase()
    {
        $ctry = new T_Geo_Country(13,'gb','United Kingdom','uk');
        $this->assertSame('GB',$ctry->getCode());
    }

    function testCodeCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame($f->transform('GB'),$ctry->getCode($f));
    }

    function testNameSetInConstructor()
    {
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame('United Kingdom',$ctry->getName());
    }

    function testNameCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame($f->transform('United Kingdom'),$ctry->getName($f));
    }

    function testUrlSetInConstructor()
    {
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame('uk',$ctry->getUrl());
    }

    function testUrlCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $ctry = new T_Geo_Country(13,'GB','United Kingdom','uk');
        $this->assertSame($f->transform('uk'),$ctry->getUrl($f));
    }

}