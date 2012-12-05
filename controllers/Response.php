<?php
/**
 * Contains the T_Response class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Request Response.
 *
 * This class encapsulates the reponse to any request. For HTTP for example, it
 * encapsulates page headers, content sending, etc. Every response has the
 * capability to use intercepting filters that can be attached at any time.
 *
 * @package controllers
 */
class T_Response extends Exception
{

    /**
     * Array of filter objects.
     *
     * @var array
     */
    protected $filter = array();

    /**
     * Whether the response has been aborted.
     *
     * @var bool
     */
    protected $is_aborted = false;

    /**
     * Status code.
     *
     * @var int
     */
    protected $status = 200;

    /**
     * Content of response.
     *
     * @var array
     */
    protected $content = null;

    /**
     * Response content type.
     *
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * Character encoding of the (sent) request.
     *
     * @var string
     */
    protected $encoding = T_CHARSET;

    /**
     * Response protocol.
     *
     * @var string
     */
    protected $protocol = 'HTTP/1.1';

    /**
     * Response additional headers.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Initialise response, default empty content 200 (OK).
     *
     * @param int $status  status code of response
     * @param string $content  content of the response
     */
    function __construct($status=200)
    {
        parent::__construct();
        $this->status = $status;
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $this->protocol = $_SERVER['SERVER_PROTOCOL'];
        }
    }

    /**
     * Append a filter to the end of the current filter chain.
     *
     * @param T_Response_Filter $filter  filter to apply
     * @param mixed $key  optional filter access key
     */
    function appendFilter(T_Response_Filter $filter,$key=null)
    {
        if (!is_null($key) && !array_key_exists($key,$this->filter)) {
            $this->filter[$key] = $filter;
        } elseif (is_null($key)) {
            $this->filter[] = $filter;
        } else {
            throw new InvalidArgumentException("existing key $key");
        }
        /* filter is stored BEFORE pre-filtering in case a redirect is thrown in
           the prefilter process. */
        $filter->preFilter($this);
    }

    /**
     * Get the reference to a particular intercepting filter object.
     *
     * @param mixed $key  access key
     * @return T_Response_Filter  filter instance
     */
    function filter($key)
    {
        if (array_key_exists($key,$this->filter)) {
            return $this->filter[$key];
        } else {
            throw new InvalidArgumentException("key $key doesn't exist");
        }
    }

    /**
     * Apply prepare filters.
     *
     * @return T_Response  fluent interface
     */
    protected function prepareFilters()
    {
        foreach ($this->filter as $f) {
        	$f->prepareFilter($this);
        }
        return $this;
    }

    /**
     * Apply post filters in reverse order.
     *
     * @return T_Response  fluent interface
     */
    function closeFilters()
    {
        $filters = array_reverse($this->filter);
        foreach ($filters as $f) {
        	$f->postFilter($this);
        }
        return $this;
    }

    /**
     * Abort the response (must be caled before creating a new response).
     */
    function abort()
    {
        $filters = array_reverse($this->filter);
        foreach ($filters as $f) {
        	$f->abortFilter($this);
        }
        $this->is_aborted = true;
    }

    /**
     * Whether the response has been aborted.
     *
     * @return bool
     */
    function isAborted() {
        return $this->is_aborted;
    }

    /**
     * Add a header key:value pair.
     *
     * Each header is sent as "key : value" on response send. This function
     * automatically detects changes to encoding and content type and modifies
     * the internal variables storing these values.
     *
     * @param string $key  header key (e.g. 'Content-Type','Location')
     * @param string $value  header value
     */
    function setHeader($key,$value)
    {
        $key = _transform($key,new T_Filter_HeaderKey('mb_trim'));
        // detect content-type (optionally with encoding)
        if (strcasecmp($key,'Content-Type')===0) {
            $charset = new T_Pattern_Regex('/^(.*);\s?charset\s?=\s?([^\s]+)$/');
            if ($match = $charset->getFirstMatch($value)) {
                $this->contentType = $match[1];
                $this->encoding = $match[2];
            } else {
                $this->contentType = $value;
            }
        } else {
            $this->headers[$key] = $value;
        }
    }

    /**
     * Set the page status.
     *
     * @param int $status  response status
     */
    function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get the page status.
     *
     * @return int  page status code
     */
    function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the character encoding.
     *
     * @param string $encoding  character encoding
     */
    function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Get content.
     *
     * @return mixed $content  content string or view
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * This function discards all previously set content.
     *
     * @param mixed $content  content string or view
     */
    function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns the HTTP status message.
     *
     * @param int $code  status code
     * @return string  status message
     */
    protected function getStatusMsg($code)
    {
        switch ($code) {
            case 201 :  return 'Created';
            case 202 :  return 'Accepted';
            case 204 :  return 'No Content';
            case 206 :  return 'Partial Content'; 
            case 304 :  return 'Not Modified';
            case 400 :  return 'Bad Request';
            case 401 :  return 'Unauthorized';
            case 403 :  return 'Forbidden';
            case 404 :  return 'Not Found';
            case 405 :  return 'Method Not Allowed';
            case 406 :  return 'Not Acceptable';
            case 410 :  return 'Gone';
            case 412 :  return 'Precondition Failed';
            case 500 :  return 'Internal Server Error';
            case 501 :  return 'Not Implemented';
            case 502 :  return 'Bad Gateway';
            case 503 :  return 'Service Unavailable';
            case 504 :  return 'Gateway Timeout';
            default  :  return '';
        }
    }

    /**
     * Sends a single header.
     *
     * @param string $header  header string to send
     */
    protected function sendHeader($header)
    {
        header($header);
    }

    /**
     * Send the response status.
     *
     * The way the status is sent depends on the mode in which PHP is running.
     * The standard way is to send the something like 'HTTP/1.1 404 Not Found',
     * however in CGI mode PHP doesn't sent the response correctly (bug #27345)
     * and instead the status must be sent as 'Status: 404 Not found'.
     */
    protected function sendStatus()
    {
        $statusmsg = $this->getStatusMsg($this->status);
        if (strlen($statusmsg)==0 || strpos(php_sapi_name(),'cgi')===false) {
            $this->sendHeader( $this->protocol.' '.$this->status.
                               ($statusmsg ? ' '.$statusmsg : '')  );
        } else {
            $this->sendHeader('Status: '.$this->status.' '.$statusmsg);
        }
    }

    /**
     * Send the response headers.
     */
    protected function sendAllHeaders()
    {
        $ini = 'Content-Type:'.$this->contentType.'; charset='.$this->encoding;
        $this->sendHeader($ini);
        foreach ($this->headers as $key => $value) {
            $this->sendHeader($key . ': ' . $value);
        }
    }

    /**
     * Send the response body.
     */
    protected function sendBody()
    {
        ob_start();
          // ^ e.g. session headers added, issued as part of content render.
        if (strcasecmp($this->encoding,T_CHARSET)!=0) {
            throw new Exception('Different encoding not implemented yet.');
        } else {
            if (interface_exists('T_View')
                && ($this->content instanceof T_View)) { // from views
                $this->content->toBuffer();
            } else {
                echo $this->content;
            }
        }
        ob_end_flush();
    }

    /**
     * Send the entire response.
     */
    function send()
    {
        $this->prepareFilters();
        $this->sendStatus();
        $this->sendAllHeaders();
        $this->sendBody();
        $this->closeFilters();
    }

}
