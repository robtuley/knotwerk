<?php
class T_Test_Filter_GeoCodeAddress extends T_Test_Filter_SkeletonHarness
{

    function testFullAddressIsGeoCodedWithCountryBias()
    {
        $addr = new T_Geo_Address('Line 1','Line 2','City','State','POST CODE',
                                  new T_Geo_Country(null,'GB','United Kingdom','united-kingdom'));
        $filter = new T_Filter_GeoCodeAddress($stub=new T_Test_Filter_GeoCodeStub());
        $test = $filter->transform($addr);
        
        $this->assertNotSame($addr,$test);  // clones address
        $this->assertSame($stub->query,'Line 1, Line 2, City, State, POST CODE');
        $this->assertSame($stub->bias,'GB');
        $this->assertSame($stub->expect->getLatitude(),$test->getLatitude());
        $this->assertSame($stub->expect->getLongitude(),$test->getLongitude());
        $this->assertSame($stub->expect->getAltitude(),$test->getAltitude());
    }
    
    function testSparseAddressIsGeoCodedWithoutCountryBias()
    {
        $addr = new T_Geo_Address('Line 1',null,'City',null,'POST CODE',null);
        $filter = new T_Filter_GeoCodeAddress($stub=new T_Test_Filter_GeoCodeStub());
        $test = $filter->transform($addr);
        
        $this->assertNotSame($addr,$test);  // clones address
        $this->assertSame($stub->query,'Line 1, City, POST CODE');
        $this->assertSame($stub->bias,false);
        $this->assertSame($stub->expect->getLatitude(),$test->getLatitude());
        $this->assertSame($stub->expect->getLongitude(),$test->getLongitude());
        $this->assertSame($stub->expect->getAltitude(),$test->getAltitude());
    }
    
    function testGeoCodeFailureResultsInUserFriendlyErrorMessage()
    {
        $addr = new T_Geo_Address('Line 1',null,'City',null,'POST CODE',null);
        $filter = new T_Filter_GeoCodeAddress($stub=new T_Test_Filter_GeoCodeStub());
        $stub->exception = new T_Exception_Filter('driver_err');
        try {
            $filter->transform($addr);
            $this->fail();
        } catch (T_Exception_Filter $e) {
            $this->assertNotContains('driver_err',$e->getMessage());
        }
    }
    
}