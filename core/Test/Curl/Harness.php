<?php
abstract class T_Test_Curl_Harness extends T_Unit_Case
{

    function setUp()
    {
        if (!$this->getFactory()->isNetwork()) {
            $this->skip('Network must be enabled in config to test CURL');
        }
    }


}