<?php
class T_Test_Exception_Auth extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Auth('msg');
            $this->fail();
        } catch (T_Exception_Auth $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}
