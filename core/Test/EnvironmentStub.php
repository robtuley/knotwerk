<?php
class T_Test_EnvironmentStub extends T_Environment_Http implements T_Test_Stub
{

    function __construct(array $input=array())
    {
        $this->willUse($this); // register self for factory population
        $this->input = $input;
          // note that the parent constructor is never called as we DO NOT
          // want to setup the error handling or autoloading as this is simply
          // a test stub environment.
    }

    protected function parseInput()
    {
        throw BadFunctionCallException("No input parsing required");
    }

    function setInput($key,$value)
    {
        $this->input[$key] = $value;
        return $this;
    }

    function setRequest($url,$method)
    {
        $this->url = $url;
        $this->method = $method;
        return $this;
    }

}
