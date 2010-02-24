<?php
/**
 * Unit test cases for the T_Email_Text class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Email_Text test cases.
 *
 * @package coreTests
 */
class T_Test_Email_Text extends T_Unit_Case
{

    /**
     * Email driver.
     *
     * @var T_Test_Email_DriverStub
     */
    protected $driver;

    /**
     * Set test email driver.
     */
    function setUp()
    {
        $this->driver = new T_Test_Email_DriverStub();
    }

    function testFromAddressSetInConstructor()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $email->send();
        $this->assertSame('from',$this->driver->from);
    }

    function testToAddressSetInConstructor()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $email->send();
        $this->assertSame(array('to'),$this->driver->to);
    }

    function testSubjectSetInConstructor()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $email->send();
        $this->assertSame('subject',$this->driver->subject);
    }

    function testBodyContentSetInConstructor()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $email->send();
        $this->assertSame('content',$this->driver->body);
    }

    function testNoCcAddressesByDefault()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $email->send();
        $this->assertSame(array(),$this->driver->cc);
    }

    function testNoBccAddressesByDefault()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $email->send();
        $this->assertSame(array(),$this->driver->bcc);
    }

    function testSendMethodHasFluentInterface()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $test = $email->send();
        $this->assertSame($email,$test);
    }

    function testToMethodHasFluentInterface()
    {
        $email = new T_Email_Text('from','to1','subject','content',$this->driver);
        $test = $email->to('to2');
        $this->assertSame($email,$test);
    }

    function testToMethodAddsExtraToAddresses()
    {
        $email = new T_Email_Text('from','to1','subject','content',$this->driver);
        $email->to('to2')->to('to3')->send();
        $this->assertSame(array('to1','to2','to3'),$this->driver->to);
    }

    function testCcMethodHasFluentInterface()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $test = $email->cc('cc');
        $this->assertSame($email,$test);
    }

    function testCcMethodAddsExtraCcAddresses()
    {
        $email = new T_Email_Text('from','to1','subject','content',$this->driver);
        $email->cc('cc1')->cc('cc2')->send();
        $this->assertSame(array('cc1','cc2'),$this->driver->cc);
    }

    function testBccMethodHasFluentInterface()
    {
        $email = new T_Email_Text('from','to','subject','content',$this->driver);
        $test = $email->bcc('bcc');
        $this->assertSame($email,$test);
    }

    function testBccMethodAddsExtraBccAddresses()
    {
        $email = new T_Email_Text('from','to1','subject','content',$this->driver);
        $email->bcc('bcc1')->bcc('bcc2')->send();
        $this->assertSame(array('bcc1','bcc2'),$this->driver->bcc);
    }


}
