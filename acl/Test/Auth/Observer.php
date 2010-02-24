<?php
class T_Test_Auth_Observer implements T_Test_Stub,T_Auth_Observer
{

    protected $pass = false;
    protected $fail = false;

    function pass(T_Auth $auth)
    {
        $this->pass = $auth;
    }

    function fail(T_Auth $auth)
    {
        $this->fail = $auth;
    }

    function getPass()
    {
        return $this->pass;
    }

    function getFail()
    {
        return $this->fail;
    }

}
