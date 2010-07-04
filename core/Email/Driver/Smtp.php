<?php
/**
 * Defines the T_Email_Driver_Smtp class.
 *
 * @package core
 */
class T_Email_Driver_Smtp implements T_Email_Driver
{

    const CRLF = "\r\n";
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $timeout;
    protected $socket = false;

    function __construct($host,$port,$user,$pass,$timeout=5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->timeout = $timeout;
    }

    function send($from,array $to,array $cc,array $bcc,$subject,$body)
    {
        // prepare headers
        $headers = array();
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
        if (count($to)>0) $headers[] = 'To: '.implode(',',$to);
        if (count($cc)>0) $headers[] = 'Cc: '.implode(',',$cc);
        if (count($bcc)>0) $headers[] = 'Bcc: '.implode(',',$bcc);
        $headers[] = 'X-Mailer: PHP/'.phpversion();
        if (strpos($subject,"\n")!==false)
            throw new LogicException("LF detected in subject $subject");
        $headers[] = 'Subject: '.$subject;

        // prepare body
        $body = preg_replace('/(?:\r\n|\n|\x0b|\f|\x85)/',EOL,$body);
        $body = wordwrap($body,70);

        // prepare full text
        $email = implode(self::CRLF,$headers).  // headers
                 self::CRLF.self::CRLF.         // blank line
                 $body;                         // body
        // the SMTP data protocol uses \r\n. as a data completion
        // delimiter, so we need to check this doesn't occue in the
        // text.
        $email = str_replace(self::CRLF.'.',self::CRLF.'..',$email);

        // connect (if not already) and send prepared data to SMTP server
        $this->connect()
             ->execute("MAIL FROM:<{$from}>",250);
        $rcpts = array_merge($to,$cc,$bcc);
        foreach ($rcpts as $r) $this->execute("RCPT TO:<$r>",25);
        $this->execute('DATA',354)
             ->execute($email.self::CRLF.'.',250)
             ->execute('RSET',250);

        return $this;
    }

    function __destruct()
    {
        // quit and close the socket connection.
        if (is_resource($this->socket)) {
            try { $this->execute('QUIT',221); } catch (Exception $e) {}
            @fclose($this->socket);
        }
    }

    // ----- INTERNALS ------

    protected function connect()
    {
        if (false!==$this->socket) return $this->socket; // already connected

        // connect to socket
        $this->socket = @fsockopen($this->host,$this->port,
                                   $errno,$errstr,$this->timeout);
        if (!$this->socket) {
            $msg = "SMTP connect to {$this->host}:{$this->port}: $errno $errstr";
            throw new T_Exception_Email($msg);
        }
        stream_set_timeout($this->socket,$this->timeout);
        $this->listen(); // catch and discard server greeting

        // apply authentication (eSMTP): currently only supports PLAIN,
        // @todo enable use of CRAM-MD5
        try {
            $this->execute('EHLO '.$this->host,250)
                 ->execute('AUTH LOGIN',334)
                 ->execute(base64_encode($this->user),334)
                 ->execute(base64_encode($this->pass),235);
        } catch (T_Exception_Email $e) {
            $msg = "Auth for {$this->host} failed.";
            throw new T_Exception_Email($msg,0,$e);
        }

        return $this;
    }

    protected function listen()
    {
        if (!is_resource($this->socket))
            throw new LogicException("No socket to listen to");

        // listen to the socket to try and get the data received from the
        // SMTP commands. Listent until:
        //  (a) it looks like we're not getting a response (100 iterations)
        //  (b) we detect an end of line in the response
        //  (c) the line we are reading doesn't have a space in the 4th position
        //      (because responses are "250 Accepted" type format)
        $data = $line = $i = null;
        while ( (strpos($data,self::CRLF)===false || mb_substr($line,3,1)!==' ')
                && $i<100) {
            $line = fgets($this->socket,512);
            $data .= $line;
            $i++;
        }
        return mb_trim($data);
    }

    protected function execute($command,$expect)
    {
        $expect = (string) $expect;
        if (!is_resource($this->socket))
            throw new LogicException("No socket to execute against");

        // execute command
        $ok = @fwrite($this->socket,$command.self::CRLF,strlen($command)+2);
        if (false===$ok) {
            $msg = "SMTP $command to {$this->host}:{$this->port} failed.";
            throw new T_Exception_Email($msg);
        }

        // parse out response
        $response = $this->listen();
        if (substr($response,0,strlen($expect))!==$expect) {
            $msg = "SMTP $command to {$this->host}:{$this->port} ".
                   "not $expect ($response)";
            throw new T_Exception_Email($msg);
        }

        return $this;
    }

}