<?php
class T_Test_Exception_Curl extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Curl('msg');
            $this->fail();
        } catch (T_Exception_Curl $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}