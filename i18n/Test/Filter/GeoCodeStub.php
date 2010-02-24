<?php
class T_Test_Filter_GeoCodeStub implements T_Filter_GeoCode,T_Test_Stub
{
    
    public $query = null;
    public $bias = false;
    public $expect;
    public $exception = false;
    
    function __construct()
    {
        $this->expect = new T_Geo_Point(50.56,40.67,123);
    }
    
    function biasToCountry($code)
    {
        $this->bias = $code;
        return $this;
    }
    
    function transform($query)
    {
        $this->query = $query;
        if ($this->exception) throw $this->exception;
        return $this->expect;
    }
    
}