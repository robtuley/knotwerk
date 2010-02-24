<?php
/**
 * Defines the T_Test_Email_DriverStub class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Testing emailing driver stub.
 *
 * @package core
 */
class T_Test_Email_DriverStub implements T_Email_Driver,T_Test_Stub
{

    /**
     * Arguments to send method.
     */
    public $from;
    public $to;
    public $cc;
    public $bcc;
    public $subject;
    public $body;

    /**
     * "Send" an email.
     */
    function send($from,array $to,array $cc,array $bcc,$subject,$body)
    {
        $this->from = $from;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->subject = $subject;
        $this->body = $body;
    }

}