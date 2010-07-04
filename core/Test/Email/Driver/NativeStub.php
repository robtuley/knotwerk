<?php
class T_Test_Email_Driver_NativeStub
    extends T_Email_Driver_Native implements T_Test_Stub
{


    protected $is_ok;
    
    public $primary;
    public $subject;
    public $body;
    public $header;

    function __construct($is_ok=true)
    {
        $this->is_ok = (bool) $is_ok;
    }

    protected function doSend($primary,$subject,$body,$header_str)
    {
        $this->primary = $primary;
        $this->subject = $subject;
        $this->body = $body;
        $this->header = $header_str;
        return $this->is_ok;
    }

}
