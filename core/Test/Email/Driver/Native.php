<?php
class T_Test_Email_Driver_Native extends T_Unit_Case
{

    function testSubjectPassedToSendMailFunction()
    {
        $driver = new T_Test_Email_Driver_NativeStub();
        $driver->send('from@example.com',array('to@example.com'),array(),array(),'subject','body');
        $this->assertSame('subject',$driver->subject);
    }

    function testDriverFailsWhenSubjectContainsALineReturn()
    {
        $driver = new T_Test_Email_Driver_NativeStub();
        try {
            $driver->send('from@example.com',array('to@example.com'),array(),array(),"multi\nline",'body');
            $this->fail();
        } catch (LogicException $e) {}
    }

    function testPrimaryEmailAddressIsFirstInToArray()
    {
        $driver = new T_Test_Email_Driver_NativeStub();
        $driver->send('from@example.com',array('to@example.com'),array(),array(),'subject','body');
        $this->assertSame('to@example.com',$driver->primary);
    }

    function testDriverThrowsAnExceptionWhenNativeFunctionFails()
    {
        $driver = new T_Test_Email_Driver_NativeStub(false);
        try {
            $driver->send('from@ex.com',array('to@ex.com'),array(),array(),
                          'subject','body');
            $this->fail();
        } catch (T_Exception_Email $e) {}
    }

}
