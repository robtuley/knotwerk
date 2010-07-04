<?php
/**
 * Email sending driver using inbuilt PHP mail functions.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Email_Driver_Native implements T_Email_Driver
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

        // create headers
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
        if (count($to)>0) $headers[] = 'To: '.implode(',',$to);
        if (count($cc)>0) $headers[] = 'Cc: '.implode(',',$cc);
        if (count($bcc)>0) $headers[] = 'Bcc: '.implode(',',$bcc);
        $headers[] = 'X-Mailer: PHP/'.phpversion();
        $header_str = implode("\r\n",$headers);
        if (strpos($subject,"\n")!==false)
            throw new LogicException('LF detected in subject');

        // prepare body (normalise newlines and wrap)
        $eol = '/(?:\r\n|\n|\x0b|\f|\x85)/';
            // We can't use the more efficient '\R' here as
            // it is only supported by PCRE 7.0+
        $body = preg_replace($eol,EOL,$body);
        $body = wordwrap($body,70);

        // send email
        $ok = $this->doSend($primary,$subject,$body,$header_str);
        if ($ok===false) {
            throw new T_Exception_Email("Email to $primary failed");
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