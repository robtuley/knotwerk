<?php
/**
 * Contains the T_Email_Driver interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Email sending driver.
 *
 * @package core
 */
interface T_Email_Driver
{

    /**
     * Send an email.
     *
     * @param string $from  email to send from
     * @param array $to  array of emails to send to
     * @param array $cc  array of emails to CC
     * @param array $bcc  array of emails to BCC
     * @param string $subject  email subject
     * @param string $body  body content
     */
    function send($from,array $to,array $cc,array $bcc,$subject,$body);

}