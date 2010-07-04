<?php
class T_Test_Email_Driver_Stub implements T_Email_Driver,T_Test_Stub
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