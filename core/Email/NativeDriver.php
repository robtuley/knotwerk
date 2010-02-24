<?php
/**
 * Defines the T_Email_NativeDriver class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Email sending driver using inbuilt PHP mail functions.
 *
 * @package core
 */
class T_Email_NativeDriver implements T_Email_Driver
{

    /**
     * Send an email.
     *
     * @param string $from  email to send from
     * @param array $to  array of emails to send to
     * @param array $cc  array of emails to CC
     * @param array $bcc  array of emails to BCC
     * @param string $subject  email subject
     * @param string $body  body content  (plain text)
     */
    function send($from,array $to,array $cc,array $bcc,$subject,$body)
    {
        $primary = array_shift($to);
        /* create mail headers */
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
        if (count($to)>0) $headers[] = 'To: '.implode(',',$to);
        if (count($cc)>0) $headers[] = 'Cc: '.implode(',',$cc);
        if (count($bcc)>0) $headers[] = 'Bcc: '.implode(',',$bcc);
        $headers[] = 'X-Mailer: PHP/'.phpversion();
        $header_str = implode("\r\n",$headers);
        /* check no newline characters in subject */
        if (strpos($subject,"\n")!==false) {
            throw new T_Exception_Email('newline characters not permitted in subject');
        }
        /* prepare body (normalise newlines and wrap) */
        $eol = '/(?:\r\n|\n|\x0b|\f|\x85)/';
        	/* Matches newline characters: LF, CR, CRLF and unicode linebreaks.
               We can't use the more efficient '\R' here as it is only supported
               by PCRE 7.0+  */
        $body = preg_replace($eol,EOL,$body);
        $body = wordwrap($body,70);
        /* send email */
        $ok = $this->doSend($primary,$subject,$body,$header_str);
        if ($ok===false) {
            throw new T_Exception_Email('email not sent to '.$primary);
        }
    }

    /**
     * Execute the actual mail send.
     *
     * @param string $primary  primary email address
     * @param string $subject  subject
     * @param string $body  body
     * @param string $header_str  extra headers
     * @return bool  whether was successful or not.
     */
    protected function doSend($primary,$subject,$body,$header_str)
    {
        return mb_send_mail($primary,$subject,$body,$header_str);
    }

}