<?php
/**
 * Defines T_Curl_Request class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An class to encapsulate a CURL request.
 *
 * @package core
 */
class T_Curl_Request
{

    /**
     * Curl handle.
     *
     * @var resource.
     */
    protected $handle;

    /**
     * Response body.
     *
     * @var string
     */
    protected $body = null;

    /**
     * Response body as xml.
     *
     * @var SimpleXMLElement
     */
    protected $xml = false;

    /**
     * Response headers.
     *
     * @var $headers
     */
    protected $headers = array();

    /**
     * Response code.
     *
     * @var int
     */
    protected $code = null;

    /**
     * Encapsulate a CURL request.
     *
     * @param resource $curl
     */
    function __construct(&$curl)
    {
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,false);
		curl_setopt($curl,CURLOPT_WRITEFUNCTION,array($this,'_handleCurlWrite'));
		curl_setopt($curl,CURLOPT_HEADERFUNCTION,array($this,'_handleCurlHeader'));
		curl_setopt($curl,CURLOPT_HEADER,false);
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($curl,CURLOPT_MAXREDIRS,5);
        $this->handle =& $curl;
    }

	/**
	 * This executes the CURL request.
	 *
	 * Note that this execution is a *blocking* request, and this function will
	 * not return until the request has completed. To execute in a non-blocking
	 * way, or to execute multiple curl requests in parallel, use the class
	 * T_Curl_Queue.
	 *
	 * @see T_Curl_Queue
	 * @return T_Curl_Request  fluent interface
	 */
	function execute()
	{
		if (!is_resource($this->handle)) {
			throw new T_Exception_Curl('CURL request has been exe already');
		}
		if (curl_exec($this->handle)) {
			$code = curl_getinfo($this->handle,CURLINFO_HTTP_CODE);
			$this->setCode($code);
		} else {
			$msg = curl_errno($this->handle).': '.curl_error($this->handle);
			throw new T_Exception_Curl($msg);
		}
		curl_close($this->handle);
		return $this;
	}

    /**
     * Directly access a reference to the CURL handle.
     *
     * @return resource  reference to CURL handle
     */
    function &getHandle()
    {
        return $this->handle;
    }

    /**
     * Sets the response code.
     *
     * @param int $code
     * @return T_Aws_Response  fluent
     */
    function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Gets the response code.
     *
     * @return int
     */
    function getCode()
    {
        return $this->code;
    }

    /**
     * Get the body as XML object.
     *
     * @return SimpleXMLElement
     */
    function getXml()
    {
        if (false!==$this->xml) return $this->xml;
        if (!isset($this->headers['Content-Type']) ||
            strpos($this->headers['Content-Type'],'application/xml')===false) {
			  // ^ content-type often has charset in, so check contains mime
			  //   rather than == xml mime
            throw new T_Exception_Curl("Response is not XML");
        }
        return $this->xml = simplexml_load_string($this->body);
    }

    /**
     * Get the body.
     *
     * @return string
     */
    function getBody()
    {
        return $this->body;
    }

    /**
     * Get the headers as name=>value pairs.
     *
     * @return array
     */
    function getHeaders()
    {
        return $this->headers;
    }

	/**
	 * CURL write callback
	 *
	 * @param resource &$curl  CURL
	 * @param string &$data  body data
	 * @return integer  len of data
	 */
	function _handleCurlWrite(&$curl,&$data)
    {
		$this->body .= $data;
		return strlen($data);
	}

	/**
	 * CURL header callback
	 *
	 * @param resource &$curl  CURL
	 * @param string &$data  header data
	 * @return integer
	 */
	function _handleCurlHeader(&$curl,&$data)
    {
		if (($strlen=strlen($data))<=2) return $strlen;
		if (substr($data,0,4) == 'HTTP') {
			$this->code = (int) substr($data,9,3);
		} else {
			list($header,$value) = explode(': ',trim($data),2);
			$this->headers[$header] = $value;
		}
		return $strlen;
	}

}