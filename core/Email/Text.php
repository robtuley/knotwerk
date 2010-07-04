<?php
/**
 * Text Email.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Email_Text
{

    protected $from;
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $subject;
    protected $body;
    protected $driver = null;

    /**
     * Create email.
     *
     * @param string $from  email address to send from
     * @param string $to  email address to send to
     * @param string $subject  subject line
     * @param mixed $content  email content
     */
    function __construct($from,$to,$subject,$content,T_Email_Driver $driver)
    {
        $this->from = $from;
        $this->to[] = $to;
        $this->subject = $subject;
        $this->body = $content;
        $this->driver = $driver;
    }

    /**
     * Add an additional recipient.
     *
     * @param string $email  email address
     * @return T_Email_Text  fluent interface
     */
    function to($email)
    {
        $this->to[] = $email;
        return $this;
    }

    /**
     * Add an additional CC recipient.
     *
     * @param string $email  email address
     * @return T_Email_Text  fluent interface
     */
    function cc($email)
    {
        $this->cc[] = $email;
        return $this;
    }

    /**
     * Add an additional BCC recipient.
     *
     * @param string $email  email address
     * @return T_Email_Text  fluent interface
     */
    function bcc($email)
    {
        $this->bcc[] = $email;
        return $this;
    }

    /**
     * Send email.
     *
     * @return T_Email_Text  fluent interface
     */
    function send()
    {
        $this->driver->send($this->from,$this->to,$this->cc,
                            $this->bcc,$this->subject,(string) $this->body);
        return $this;
    }

}