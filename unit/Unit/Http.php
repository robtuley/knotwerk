<?php
/**
 * Defines the class T_Unit_Http.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * HTTP "Smoke" test cases.
 *
 * @package unit
 */
class T_Unit_Http extends T_Unit_Case
{

    /**
     * Last loaded URL.
     *
     * @var string
     */
    protected $url = null;

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $code = null;

    /**
     * HTTP body.
     *
     * @var string
     */
    protected $body = null;

    /**
     * HTTP body as HTML.
     *
     * @var SimpleXmlEelement
     */
    protected $html = false;

    /**
     * HTTP headers.
     *
     * @var $headers
     */
    protected $headers = array();

    /**
     * Last validation time.
     *
     * @var int
     */
    protected $last_validation = false;

    /**
     * CURL write callback
     *
     * @param resource &$ch  CURL
     * @param string &$data  body data
     * @return integer  len of data
     */
    function handleCurlWrite(&$ch,&$data)
    {
	$this->body .= $data;
	return strlen($data);
    }

    /**
     * CURL header callback
     *
     * @param resource &$ch  CURL
     * @param string &$data  header data
     * @return integer
     */
    function handleCurlHeader(&$ch,&$data)
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

    /**
     * Load a web-page.
     *
     * @param string $url  URL
     * @param string $method  HTTP method
     * @param string $data  data to send in body
     * @param array $headers  extra headers to send (name=>value pairs)
     * @return T_Unit_Http  fluent
     */
    function load($url,$method='GET',$data=null,$headers=array())
    {
        $this->url = $url;
        $this->code = null;
        $this->body = null;
        $this->headers = array();
        $this->html = null;

	// prepare headers
	$h = array();
	foreach ($headers as $name => $val)
            if (strlen($val)>0) $h[] = $name.': '.$val;

        // prepare request
        $ch = curl_init();
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch,CURLOPT_USERAGENT,'knotwerk/php');
	if (strncmp($url,'https',5)===0) {
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
	    // turn off host verification for testing.
	}
	curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$h);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,false);
	curl_setopt($ch,CURLOPT_WRITEFUNCTION,array($this,'handleCurlWrite'));
	curl_setopt($ch,CURLOPT_HEADERFUNCTION,array($this, 'handleCurlHeader'));
	curl_setopt($ch,CURLOPT_HEADER,false);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        switch ($method) {
	    case 'GET': break;
	    case 'PUT':
            case 'POST':
                if (preg_match('@^[a-z0-9]+'.preg_quote('://').'@i',$data)) {
                    // input is a stream, so assume PUT
                    curl_setopt($ch,CURLOPT_PUT,true);
                    $fp = fopen($data,'rb');  // assume data is file to put
                    curl_setopt($ch,CURLOPT_INFILE,$fp);
                    curl_setopt($ch,CURLOPT_INFILESIZE,filesize($content));
                } else {
                    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
                    $len = strlen($content);
                    if ($len>0) {
                        curl_setopt($ch,CURLOPT_POSTFIELDS,$content);
                    }
                }
                break;
	    case 'HEAD':
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
		curl_setopt($ch,CURLOPT_NOBODY, true);
		break;
	    case 'DELETE':
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
		break;
	    default:
                throw new RuntimeException("Invalid HTTP method $method");
	}

	// exec request
	if (curl_exec($ch)) {
	    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	    $this->code = (int) $code;
	} else {
	    $msg = curl_errno($ch).': '.curl_error($ch);
	    throw new RuntimeException($msg);
	}
	@curl_close($ch);

        return $this;
    }

    /**
     * Gets the body as HTML
     *
     * @return SimpleXMLElement  HTML DOM
     */
    function getHtml()
    {
        if (!$this->html) {
            if (is_null($this->html) && $this->body) {
                $dom = @DOMDocument::loadHTML($this->body);
                if ($dom) {
                    $this->html = simplexml_import_dom($dom);
                } else {
                    $this->html = false;
                }
            }
            if (!$this->html) {
                $msg = "{$this->url} cannot be loaded as HTML";
                throw new RuntimeException($msg);
            }
        }
        return $this->html;
    }

    /**
     * Gets a body element.
     *
     * @param string $xpath  XPath query
     * @return SimpleXMLElement
     */
    function getElement($xpath)
    {
        return $this->getHtml()->xpath($xpath);
    }

    /**
     * Get a header element.
     *
     * @param string $name
     * @return string  value
     */
    function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Assert status code.
     *
     * @param int $code  status code
     * @return T_Unit_Http  fluent
     */
    function assertStatus($code)
    {
        $this->assertEquals($this->code,$code,"{$this->url} HTTP status code");
        return $this;
    }

    /**
     * Assert element(s) are at least present.
     *
     * @param string $xpath  XPath query
     * @return T_Unit_Http  fluent
     */
    function assertIsElement($xpath)
    {
        if (count($this->getHtml()->xpath($xpath))==0) {
            $msg = "{$this->url} does not contain element $xpath";
            throw new T_Exception_AssertFail($msg);
        }
        return $this;
    }

    /**
     * Asserts that the current page validates against it's DOCTYPE.
     *
     * This makes a HEAD request to the W3C validation service to validate the HTML
     * body of the page. It requires network and if there isn't any the assertion
     * will be skipped.
     *
     * @return T_Unit_Http
     */
    function assertValidHtml()
    {
        if (!$this->getFactory()->isNetwork()) {
            $msg = "Network must be enabled in config for HTML validation";
            $this->skip($msg);
        }

        // throttle request rate to 1 validation per second, as requested by
        // the W3C.
        $now = microtime(true);
        if (false!==$this->last_validation && ($now-$this->last_validation)<1) {
            usleep(($now-$this->last_validation)*1000000);
        }
        $this->last_validation = $now;

        // build CURL request to W3C validator
        $params = array(
            'fragment' => $this->body,
            'output' => 'soap12',
        );
        $url = 'http://validator.w3.org/check';
        $ch = curl_init();
	curl_setopt($ch,CURLOPT_USERAGENT,'knotwerk/php');
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_REFERER,'');
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,30);

        // execute curl request
        if (!($result=curl_exec($ch))) {
	    $msg = curl_errno($ch).': '.curl_error($ch);
	    throw new RuntimeException($msg);
	}
	@curl_close($ch);

        // process the response
        $xml = @DOMDocument::loadXML($result);
        if (!$xml) throw new RuntimeException('Malformed W3C XML response');
        $xpath = new DOMXpath($xml);
        $xpath->registerNamespace('m','http://www.w3.org/2005/10/markup-validator');
        $elements = $xpath->query('//m:errorcount');
        $errors = false;
        if($elements->item(0) && $elements->item(0)->nodeValue > 0) {
            $msgs = $xpath->query('//m:errors/m:errorlist/m:error/m:message');
            foreach ($msgs as $node) {
                $errors .= $node->nodeValue.', ';
            }
        }

        // report any errors
        if (false!==$errors) {
            $errors = rtrim($errors,', ');
            $this->fail("{$this->url} failed W3C HTML validator by $errors");
        }
        return $this;
    }

}
