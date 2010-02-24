<?php
/**
 * Contains the T_Url_Xhtml class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An XHTML URL.
 *
 * This class encapsulates an XHTML URL to a different resource.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Url_Xhtml extends T_Url
{

    /**
     * Link title.
     *
     * @var string
     */
    protected $title;

    /**
     * Change frequencey of the page.
     *
     * @var string
     */
    protected $change_freq = null;

    /**
     * URL priority.
     *
     * @var float
     */
    protected $priority = 0.5;

    /**
     * Attributes e.g. 'accesskey','class'.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Create a URL.
     *
     * @param string $title  title of the URL link
     * @param string $scheme  URL scheme (e.g. http,https,etc.)
     * @param string $host  URL host (e.g. subdomain.domain:port)
     * @param array $path  URL path as a segment array
     * @param array $parameters  URL parameters as name=>value pair array
     * @param string $fragment  URL fragment (e.g. HTTP anchor value)
     */
    function __construct($title,
                         $scheme,
                         $host,
                         array $path=array(),
                         array $parameters=array(),
                         $fragment=null)
    {
        parent::__construct($scheme,$host,$path,$parameters,$fragment);
        $this->title = (string) $title;
    }

    /**
     * Gets the title.
     *
     * @return string  URL link title
     */
    function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title  URL link title
     * @return T_Url_Xhtml  fluent interface
     */
    function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gets change frequency.
     *
     * This gets the change frequency of the URL. This is relevant when
     * generating sitemap XML documents.
     *
     * @see http://www.sitemaps.org/protocol.php
     * @return string  URL change frequency
     */
    function getChangeFreq()
    {
        return $this->change_freq;
    }

    /**
     * Sets the change freqency.
     *
     * Sets how frequently the URL is likely to change. This value provides
     * general information to search engines and may not correlate exactly to
     * how often they crawl the page. Valid values are:
     *
     *   - always
     *   - hourly
     *   - daily
     *   - weekly
     *   - monthly
     *   - yearly
     *   - never
     *
     * The value "always" should be used to describe documents that change each
     * time they are accessed. The value "never" should be used to describe
     * archived URLs.
     *
     * Please note that the value of this tag is considered a hint and not a
     * command. Even though search engine crawlers may consider this information
     * when making decisions, they may crawl pages marked "hourly" less
     * frequently than that, and they may crawl pages marked "yearly" more
     * frequently than that. Crawlers may periodically crawl pages marked
     * "never" so that they can handle unexpected changes to those pages.
     *
     * @param string $key  URL change frequency
     * @return T_Url_Xhtml  fluent interface
     */
    function setChangeFreq($rate)
    {
        $valid = array('always','hourly','daily','weekly',
                       'monthly','yearly','never');
        $rate = strtolower($rate);
        if (!in_array($rate,$valid)) {
            throw new InvalidArgumentException("invalid change freq $rate");
        }
        $this->change_freq = $rate;
        return $this;
    }

    /**
     * Gets the URL priority.
     *
     * @return float  URL priority
     */
    function getPriority()
    {
        return $this->priority;
    }

    /**
     * Sets the URL priority.
     *
     * Sets the priority of this URL relative to other URLs on your site. Valid
     * values range from 0.0 to 1.0. This value does not affect how your pages
     * are compared to pages on other sites—it only lets the search engines
     * know which pages you deem most important for the crawlers.
     *
     * The default priority of a page is 0.5.
     *
     * Please note that the priority you assign to a page is not likely to
     * influence the position of your URLs in a search engine's result pages.
     * Search engines may use this information when selecting between URLs on
     * the same site, so you can use this tag to increase the likelihood that
     * your most important pages are present in a search index.
     *
     * Also, please note that assigning a high priority to all of the URLs on
     * your site is not likely to help you. Since the priority is relative, it
     * is only used to select between URLs on your site.
     *
     * @param float $priority  URL priority
     * @return T_Url_Xhtml  fluent interface
     */
    function setPriority($priority)
    {
        $priority = (float) $priority;
        if (0.0 > $priority || 1.0 < $priority) {
            throw new InvalidArgumentException('1.0 >= priority >= 0.0');
        }
        $this->priority = $priority;
        return $this;
    }

    /**
     * Creates a new URL from this one by appending a path.
     *
     * This creates and returns a new URL instance with the appended path but
     * empty title, parameters, fragment, description, etc.
     *
     * @param string $segment  path segment to append to URL
     * @param string $title  new title to give link
     * @param string $classname  classname to base new URL on
     * @return T_Url_Xhtml  new instance with new title and appended path
     */
    function createByAppendPath($segment,$title,$classname=null)
    {
        $classname = is_null($classname) ? get_class($this) : $classname;
        $path = $this->getPath();
        $path[] = $segment;
        return new $classname($title,$this->getScheme(),$this->getHost(),$path);
    }

    /**
     * Creates a new URL from this instance by adding a fragment.
     *
     * This creates and returns a new URL instance with the same URL apart from
     * an added fragment anchor.
     *
     * @param string $fragment  path fragment to add to URL
     * @param string $title  new title to give link
     * @param string $classname  classname to base new URL on
     * @return T_Url_Xhtml  new instance with new title and fragment
     */
    function createByFragment($fragment,$title,$classname=null)
    {
        $classname = is_null($classname) ? get_class($this) : $classname;
        return new $classname($title,$this->getScheme(),$this->getHost(),
                              $this->getPath(),array(),$fragment);
    }

    /**
     * Sets an attribute on URL.
     *
     * @param string $name  attribute name
     * @param mixed  $value  attribute value
     * @return T_Url_Xhtml  fluent
     */
    function setAttribute($name,$value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Gets the value of an attribute.
     *
     * @param string $name   attribute name
     * @return mixed
     */
    function getAttribute($name)
    {
        return array_key_exists($name,$this->attributes) ? $this->attributes[$name] : null;
    }

    /**
     * Gets the values of all set attributes.
     *
     * @return array   attribute name=>value pairs
     */
    function getAllAttributes()
    {
        return $this->attributes;
    }

    /**
     * Renders link as XHTML string.
     *
     * @return string  rendered XHTML string.
     */
    function __toString()
    {
        $attrib = $this->getAllAttributes();
        $attrib['href'] = $this->getUrl();
        $title = $this->getTitle();
        $f = new T_Filter_Xhtml;
        $out = '<a';
        foreach ($attrib as $key => $val) {
            $out .= ' '.$key.'="'._transform($val,$f).'"';
        }
        $out .= '>'._transform($title,$f).'</a>';
        return $out;
    }

}
