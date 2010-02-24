<?php
/**
 * Unit test cases for the T_Email_NativeDriver class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Email_NativeDriver test cases.
 *
 * @package coreTests
 */
class T_Test_Email_NativeDriver extends T_Unit_Case
{

    function testSubjectPassedToSendMailFunction()
    {
        $driver = new T_Test_Email_NativeDriverStub();
        $driver->send('from@example.com',array('to@example.com'),array(),array(),'subject','body');
        $this->assertSame('subject',$driver->subject);
    }

    function testDriverFailsWhenSubjectContainsALineReturn()
    {
        $driver = new T_Test_Email_NativeDriverStub();
        try {
            $driver->send('from@example.com',array('to@example.com'),array(),array(),"multi\nline",'body');
            $this->fail();
        } catch (T_Exception_Email $e) {}
    }

    function testPrimaryEmailAddressIsFirstInToArray()
    {
        $driver = new T_Test_Email_NativeDriverStub();
        $driver->send('from@example.com',array('to@example.com'),array(),array(),'subject','body');
        $this->assertSame('to@example.com',$driver->primary);
    }

    function testDriverThrowsAnExceptionWhenNativeFunctionFails()
    {
        $driver = new T_Test_Email_NativeDriverStub(false);
        try {
            $driver->send('from@ex.com',array('to@ex.com'),array(),array(),
                          'subject','body');
            $this->fail();
        } catch (T_Exception_Email $e) {}
    }




}
