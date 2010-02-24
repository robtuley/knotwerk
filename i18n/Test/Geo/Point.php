<?php
/**
 * Unit test cases for the T_Geo_Point class.
 *
 * @package geoTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Geo_Point unit test cases.
 *
 * @package geoTests
 */
class T_Test_Geo_Point extends T_Unit_Case
{

    function testLongitudeSetInConstructor()
    {
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame(40.23,$pt->getLongitude());
    }

    function testLongitudeCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame($f->transform(40.23),$pt->getLongitude($f));
    }

    function testLongitudeCanBeChanged()
    {
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame($pt,$pt->setLongitude(57.9),'fluent interface');
        $this->assertSimilarFloat(57.9,$pt->getLongitude());
    }

    function testLatitudeSetInConstructor()
    {
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame(50.46,$pt->getLatitude());
    }

    function testLatitudeCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame($f->transform(50.46),$pt->getLatitude($f));
    }

    function testLatitudeCanBeChanged()
    {
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame($pt,$pt->setLatitude(57.9),'fluent interface');
        $this->assertSimilarFloat(57.9,$pt->getLatitude());
    }

    function testAltitudeIsNullByDefault()
    {
        $pt = new T_Geo_Point(40.23,50.46);
        $this->assertSame(null,$pt->getAltitude());
    }

    function testAltitudeSetInConstructor()
    {
        $pt = new T_Geo_Point(40.23,50.46,231);
        $this->assertSame(231,$pt->getAltitude());
    }

    function testAltitudeCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $pt = new T_Geo_Point(40.23,50.46,231);
        $this->assertSame($f->transform(231),$pt->getAltitude($f));
    }

    function testAltitudeCanBeChanged()
    {
        $pt = new T_Geo_Point(40.23,50.46,547);
        $this->assertSame($pt,$pt->setAltitude(2400),'fluent interface');
        $this->assertSimilarFloat(2400,$pt->getAltitude());
    }

}
