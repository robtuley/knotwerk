<?php
/**
 * Unit test cases for a T_Geo_CountryGateway class.
 *
 * @package geoTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Geo_CountryGateway unit test cases.
 *
 * @package geoTests
 */
class T_Test_Geo_CountryGateway extends T_Unit_Case
{

    protected $dbs;

    function setUpSuite()
    {
        // setup DBs with necessary SQL
        $factory = $this->getFactory();
        $this->dbs = $factory->getAllDb();
        $factory->setupSqlIn(T_ROOT_DIR.'i18n/_sql/',$this->dbs);

        // create gatways to cycle over
        $gateways = array();
        foreach ($this->dbs as $db) {
            $gateways[] = new T_Geo_CountryGateway($db,
                                 new T_Factory_Di());
        }
        $this->cycleOn('gw',$gateways);
    }

    function tearDownSuite()
    {
        $this->getFactory()
             ->teardownSqlIn(T_ROOT_DIR.'i18n/_sql/',$this->dbs);
    }

    function testGetCountryFromCode($gw)
    {
        $ctry = $gw->getByCode('GB');
        $this->assertSame('GB',$ctry->getCode());
        $this->assertTrue(is_integer($ctry->getId()));
        $this->assertTrue(strlen($ctry->getName())>0);
        $this->assertTrue(strlen($ctry->getUrl())>0);
    }

    function testGetFromCodeAsCaseInsensitive($gw)
    {
        $ctry = $gw->getByCode('gB');
        $this->assertSame('GB',$ctry->getCode());
    }

    function testGetFromCodeFailure($gw)
    {
        try {
            $ctry = $gw->getByCode('xx');
            $this->fail('no exception with invalid country code');
        } catch (InvalidArgumentException $expected) {}
    }

    function testGetCountryFromUrl($gw)
    {
        $ctry = $gw->getByCode('GB');
        $test = $gw->getByUrl($ctry->getUrl());
        $this->assertEquals($ctry,$test);
    }

    function testGetFromUrlAsCaseInsensitive($gw)
    {
        $ctry = $gw->getByCode('GB');
        $test = $gw->getByUrl(strtoupper($ctry->getUrl()));
        $this->assertEquals($ctry,$test);
    }

    function testGetFromUrlFailure($gw)
    {
        try {
            $ctry = $gw->getByUrl('xx-xx');
            $this->fail('no exception with invalid url');
        } catch (InvalidArgumentException $expected) {}
    }

    function testGetCountryFromId($gw)
    {
        $ctry = $gw->getByCode('GB');
        $test = $gw->getById($ctry->getId());
        $this->assertEquals($ctry,$test);
    }

    function testGetFromIdFailure($gw)
    {
        try {
            $ctry = $gw->getById(0);
            $this->fail();
        } catch (InvalidArgumentException $expected) {}
    }

    function testGetAll($gw)
    {
        $world = $gw->getAll();
        $this->assertTrue(count($world)>0);
        foreach ($world as $ctry) {
            $this->assertTrue($ctry instanceof T_Geo_Country);
            $this->assertTrue(is_integer($ctry->getId()));
            $this->assertSame(2,strlen($ctry->getCode()));
            $this->assertTrue(strlen($ctry->getName())>0);
            $this->assertTrue(strlen($ctry->getUrl())>0);
        }
    }

}
