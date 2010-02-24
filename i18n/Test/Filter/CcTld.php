<?php
class T_Test_Filter_CcTld extends T_Test_Filter_SkeletonHarness
{

    function testConvertsNormalCountryCodeToLowerCase()
    {
        $filter = new T_Filter_CcTld();
        $this->assertSame('es',$filter->transform('ES'));
        $this->assertSame('fr',$filter->transform('FR'));
    }

    function testTreatsGBAsASpecialCase()
    {
        $filter = new T_Filter_CcTld();
        $this->assertSame('uk',$filter->transform('GB'));
    }

}
