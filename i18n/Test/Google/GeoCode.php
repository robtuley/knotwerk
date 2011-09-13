<?php
class T_Test_Google_GeoCode extends T_Unit_Case
{

    function setUp()
    {
        if (!$this->getFactory()->isNetwork()) {
            $this->skip('Network must be enabled in config to test Google geocode');
        }
    }
 
    function testBiasByCountryCodeHasAFluentInterface()
    {
        $filter = new T_Google_GeoCode;
        $this->assertSame($filter,$filter->biasToCountry('GB'));
    }
    
    function testCanGeocodeATownWithoutCountryBiasAndNoSensor()
    {
        $filter = new T_Google_GeoCode;
        $point = $filter->transform('1600 amphitheatre mountain view ca');
        $this->assertTrue($point instanceof T_Geo_Point);
        $this->assertSimilarFloat(-122.0841430,$point->getLongitude(),0.5);
        $this->assertSimilarFloat(37.4219720,$point->getLatitude(),0.5);
    }
    
    function testGeoCodingFailureResultsInException()
    {
        $filter = new T_Google_GeoCode;
        try {
            $filter->transform(''); // expect 601 = G_GEO_MISSING_QUERY response
            $this->fail();
        } catch (T_Exception_Filter $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }
    
    function testCanBiasGeocodedResultsToACountry()
    {
        $filter = new T_Google_GeoCode(true);
        
        // defaults to Toledo, OH, USA
        $point = $filter->transform('Toledo');
        $this->assertSimilarFloat(-83.5777820,$point->getLongitude(),0.5);
        $this->assertSimilarFloat(41.6529200,$point->getLatitude(),0.5);
        
        // bias to Spain, now returns 
        $filter->biasToCountry('ES');
        $point = $filter->transform('Toledo');
        $this->assertSimilarFloat(-4.0244759,$point->getLongitude(),0.5);
        $this->assertSimilarFloat(39.8567775,$point->getLatitude(),0.5);
        
        // check can switch *back*
        $filter->biasToCountry(false);
        $point = $filter->transform('Toledo');
        $this->assertSimilarFloat(-83.5777820,$point->getLongitude(),0.5);
        $this->assertSimilarFloat(41.6529200,$point->getLatitude(),0.5);
    }
    
}