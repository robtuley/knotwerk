<?php
/**
 * Contains the T_Url class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A URL.
 *
 * This class encapsulates a URL to a resource. A URL can be divided into
 * a number of components:
 *
 * <samp>
 *    foo://example.com:8042/over/there?name=ferret#nose
 *    \_/   \______________/\_________/ \_________/ \__/
 *     |           |            |            |        |
 *  scheme        host         path        query   fragment
 * </samp>
 *
 * @see http://gbiv.com/protocols/uri/rfc/rfc3986.html
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Url
{

    const AS_DIR = 1;
    const NO_HOST = 2;

    /**
     * URL scheme.
     *
     * The scheme of a URL determines what the resource being requested is. Some
     * common schemes include:
     *
     * http == Hypertext Transfer Protocol
     * https == Hypertext Transfer Protocol Secure
     * ftp == File Transfer Protocol
     * callto == telephone number
     * mailto == email address
     *
     * @see http://esw.w3.org/topic/UriSchemes
     * @var string
     */
    protected $scheme;

    /**
     * Host.
     *
     * The host is where the data is to be requested from and is made up of the
     * domain, any subdomain and port name (if no port is specified, the default
     * http 80 port is assummed in general). format is subdomain.domain:port.
     *
     * @var string
     */
    protected $host;

    /**
     * Path to resource.
     *
     * The path is stored as an array of path segments, with each segment
     * separated by a forward slash character.
     *
     * @var array
     */
    protected $path;

    /**
     * These parameters make up the query portion of the URL.
     *
     * The parameters are stored in an array where each entry name=>value. For
     * example the query 'id=20&page=1' results in array('id'=>20,'page'=>1).
     *
     * @var array
     */
    protected $parameters;

    /**
     * Fragment required.
     *
     * The fragment usually indicates the anchor point from which to view the
     * page.
     *
     * @var string
     */
    protected $fragment;

    /**
     * Create a URL.
     *
     * @param string $scheme  URL scheme (e.g. http,https,etc.)
     * @param string $host  URL host (e.g. subdomain.domain:port)
     * @param array $path  URL path as a segment array
     * @param array $parameters  URL parameters as name=>value pair array
     * @param string $fragment  URL fragment (e.g. HTTP anchor value)
     */
    function __construct($scheme,
                         $host,
                         array $path=array(),
                         array $parameters=array(),
                         $fragment=null)
    {
        $this->scheme = strtolower($scheme);
        $this->host = strtolower(rtrim($host,'/'));
        $this->path = $path;
        $this->parameters = $parameters;
        $this->fragment = $fragment;
    }

    /**
     * Get URL as a string.
     *
     * @param function $filter  optional data filter
     * @param int $options  optional flags (T_Url::AS_DIR|T_Url::RELATIVE)
     * @return string  URL reference string
     */
    function getUrl($filter=null,$options=null)
    {
        if ($options&T_Url::NO_HOST) {
            $url = '/';
        } else {
            $url = "{$this->getScheme()}://{$this->getHost()}"; // foo://host
        }
        if (count($this->getPath())>0) { // foo://host/some/path
            $path = array_map('rawurlencode',$this->getPath());
            $url = rtrim($url,'/').'/'.implode('/',$path);
        }
        if ($options&self::AS_DIR) $url = rtrim($url,'/').'/';
        if (count($this->getParameters())>0) { // foo://host/some/path?name=value
            $url .= '?';
            foreach ($this->getParameters() as $name=>$value) {
                $url .= rawurlencode($name).'='.rawurlencode($value).'&';
            }
            $url = rtrim($url,'&');
        }
        if ($this->getFragment()) { // foo://host/some/path?name=value#anchor
            $url .= '#'.rawurlencode($this->getFragment());
        }
        return _transform($url,$filter);
    }

    /**
     * Get URL as a string.
     *
     * @return string  URL reference string
     */
    function __toString()
    {
        return $this->getUrl();
    }

    /**
     * Gets the URL scheme.
     *
     * @return string  URL scheme
     */
    function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the URL scheme.
     *
     * @param string $scheme  URL scheme to set
     * @return T_Url  fluent interface
     */
    function setScheme($scheme)
    {
        $this->scheme = (string) $scheme;
        return $this;
    }

    /**
     * Gets the URL host.
     *
     * @return string  URL host
     */
    function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the URL host.
     *
     * @param string $host  new URL host
     * @return T_Url  fluent interface
     */
    function setHost($host)
    {
        $this->host = (string) $host;
        return $this;
    }

    /**
     * Gets the URL path (in segments).
     *
     * @return array  URL path segments
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * Appends another path segment to the URL.
     *
     * @param string $segment  path segment to append to URL
     * @return T_Url  fluent interface
     */
    function appendPath($segment)
    {
        $this->path[] = (string) $segment;
        return $this;
    }

    /**
     * Sets the URL path.
     *
     * @param array $path  new URL path segments
     * @return T_Url  fluent interface
     */
    function setPath(array $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Gets the URL parameters (in name=>value pairs).
     *
     * @return array  URL parameters as name=>value pairs
     */
    function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Appends another parameter to the URL.
     *
     * @param string $name  parameter name
     * @param string $value  parameter value
     * @return T_Url  fluent interface
     */
    function appendParameter($name,$value)
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * Sets the URL parameters.
     *
     * @param array $parameters  new URL parameters as name=>value pairs
     * @return T_Url  fluent interface
     */
    function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Gets the URL anchor fragment.
     *
     * @return string  URL fragment (usually an anchor)
     */
    function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Sets the URL anchor fragment.
     *
     * @param string $fragment  URL fragment to set
     * @return T_Url  fluent interface
     */
    function setFragment($fragment)
    {
        $this->fragment = (string) $fragment;
        return $this;
    }

}