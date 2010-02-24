<?php
/**
 * Defines the T_Test_Email_NativeDriverStub class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Testing email inbuilt driver stub.
 *
 * @package core
 */
class T_Test_Email_NativeDriverStub extends T_Email_NativeDriver implements T_Test_Stub
{

    /**
     * Whether the send should fail.
     *
     * @var bool
     */
    protected $is_ok;

    /**
     * Arguments to doSend method.
     */
    public $primary;
    public $subject;
    public $body;
    public $header;

    /**
     * Create inbuilt driver stub.
     *
     * @param bool $is_ok
     */
    function __construct($is_ok=true)
    {
        $this->is_ok = (bool) $is_ok;
    }

    /**
     * "Execute" the actual mail send.
     *
     * @param string $primary  primary email address
     * @param string $subject  subject
     * @param string $body  body
     * @param string $header_str  extra headers
     * @return bool  whether was successful or not.
     */
    protected function doSend($primary,$subject,$body,$header_str)
    {
        $this->primary = $primary;
        $this->subject = $subject;
        $this->body = $body;
        $this->header = $header_str;
        return $this->is_ok;
    }

}
