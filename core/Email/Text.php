<?php
/**
 * Defines the class T_Email_Text.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Text Email.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Email_Text
{

    /**
     * Email from address.
     *
     * @var string
     */
    protected $from;

    /**
     * Emails addresses to send to.
     *
     * @var array
     */
    protected $to = array();

    /**
     * Emails addresses to CC.
     *
     * @var array
     */
    protected $cc = array();

    /**
     * Emails addresses to BCC.
     *
     * @var array
     */
    protected $bcc = array();

    /**
     * Email subject.
     *
     * @var string
     */
    protected $subject;

    /**
     * Body content.
     *
     * @var mixed
     */
    protected $body;

    /**
     * Driver to send email.
     *
     * @var T_Email_Driver
     */
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
        // normalise body to string
        if (is_object($this->body)) {
            $body = $this->body->__toString();
        } else {
            $body = (string) $this->body;
        }
        // use driver to send mail
        $this->driver->send($this->from,$this->to,$this->cc,
                            $this->bcc,$this->subject,$body);
        return $this;
    }

}
