<?php
/**
 * Unit test cases for the T_Geo_Address class.
 *
 * @package geoTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Geo_Address test cases.
 *
 * @package geoTests
 */
class T_Test_Geo_Address extends T_Unit_Case
{

    /**
     * Create address to test.
     */
    protected function createAddress($a,$b,$c,$d,$e,$f,$g=null,$h=null,$i=null)
    {
        return new T_Geo_Address($a,$b,$c,$d,$e,$f,$g,$h,$i);
    }

    function getSampleCtry()
    {
        return new T_Geo_Country(13,'GB','UK','uk');
    }

    function testFormattedStringOutput()
    {
        $a = '666a, PHP Drive';
        $b = 'MySQL Estate';
        $c = 'London';
        $d = 'Greater London';
        $e = 'POST CODE';
        $uk = $this->getSampleCtry();
        $address = array(
                          $this->createAddress($a,$b,$c,$d,$e,$uk),
                          $this->createAddress(null,$b,$c,null,$e,$uk),
                          $this->createAddress($a,null,null,null,null,$uk),
                          $this->createAddress(null,null,$c,$d,$e,$uk),
                          $this->createAddress($a,null,null,$d,$e,$uk),
                          $this->createAddress($a,$b,null,null,$e,$uk),
                          $this->createAddress($a,$b,$c,null,null,$uk),
                          $this->createAddress($a,null,$c,$d,null,$uk),
                          $this->createAddress($a,$b,$c,$d,$e,null),
                        );
        $expect = array(
                         $a.EOL.$b.EOL.$c.', '.$d.EOL.$e.EOL.$uk->getName(),
                         $b.EOL.$c.EOL.$e.EOL.$uk->getName(),
                         $a.EOL.$uk->getName(),
                         $c.', '.$d.EOL.$e.EOL.$uk->getName(),
                         $a.EOL.$d.EOL.$e.EOL.$uk->getName(),
                         $a.EOL.$b.EOL.$e.EOL.$uk->getName(),
                         $a.EOL.$b.EOL.$c.EOL.$uk->getName(),
                         $a.EOL.$c.', '.$d.EOL.$uk->getName(),
                         $a.EOL.$b.EOL.$c.', '.$d.EOL.$e,
                       );

        $f = new T_Test_Filter_Suffix();
        for ($i=0; $i<count($address); $i++) {
            $addr = $address[$i];
            $this->assertSame($expect[$i],$addr->__toString());
            $this->assertSame($expect[$i],$addr->asString());
            $this->assertSame($f->transform($expect[$i]),$addr->asString($f));
        }

    }

    function testLineOneSetInConstructor()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame('Line 1',$addr->getLineOne());
    }

    function testLineOneCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($f->transform('Line 1'),$addr->getLineOne($f));
    }

    function testLineOneCanBeChanged()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($addr,$addr->setLineOne('Alt'),'fluent interface');
        $this->assertSame('Alt',$addr->getLineOne());
    }

    function testLineTwoSetInConstructor()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame('Line 2',$addr->getLineTwo());
    }

    function testLineTwoCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($f->transform('Line 2'),$addr->getLineTwo($f));
    }

    function testLineTwoCanBeChanged()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($addr,$addr->setLineTwo('Alt'),'fluent interface');
        $this->assertSame('Alt',$addr->getLineTwo());
    }

    function testCitySetInConstructor()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame('City',$addr->getCity());
    }

    function testCityCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($f->transform('City'),$addr->getCity($f));
    }

    function testCityCanBeChanged()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($addr,$addr->setCity('Alt'),'fluent interface');
        $this->assertSame('Alt',$addr->getCity());
    }

    function testStateSetInConstructor()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame('State',$addr->getState());
    }

    function testStateCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($f->transform('State'),$addr->getState($f));
    }

    function testStateCanBeChanged()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($addr,$addr->setState('Alt'),'fluent interface');
        $this->assertSame('Alt',$addr->getState());
    }

    function testPostcodeSetInConstructor()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame('P CODE',$addr->getPostCode());
    }

    function testPostcodeCanBeFilteredOnRetrieval()
    {
        $f = new T_Test_Filter_Suffix();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($f->transform('P CODE'),$addr->getPostcode($f));
    }

    function testPostcodeCanBeChanged()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($addr,$addr->setPostcode('Alt'),'fluent interface');
        $this->assertSame('Alt',$addr->getPostcode());
    }

    function testCountrySetInConstructor()
    {
        $uk = $this->getSampleCtry();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',$uk);
        $this->assertSame($uk,$addr->getCountry());
    }

    function testCountryCanBeNull()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame(null,$addr->getCountry());
    }

    function testCountryCanBeChanged()
    {
        $uk = $this->getSampleCtry();
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame($addr,$addr->setCountry($uk),'fluent interface');
        $this->assertSame($uk,$addr->getCountry());
    }

    function testParentPointDataNullByDefault()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null);
        $this->assertSame(null,$addr->getLongitude());
        $this->assertSame(null,$addr->getLatitude());
        $this->assertSame(null,$addr->getAltitude());
    }

    function testParentPointDataSetInConstructor()
    {
        $addr = $this->createAddress('Line 1','Line 2','City','State','P CODE',null,40.23,50.46,123);
        $this->assertSame(40.23,$addr->getLongitude());
        $this->assertSame(50.46,$addr->getLatitude());
        $this->assertSame(123,$addr->getAltitude());
    }

}
