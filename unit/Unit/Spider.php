<?php
/**
 * Defines the class T_Unit_Spider.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * HTTP test cases which propagate through a site via spidering.
 *
 * @package unit
 */
class T_Unit_Spider extends T_Unit_Http
{

    /**
     * Root URL for spider.
     *
     * @var string
     */
    protected $base_url = false;

    /**
     * Tested URLs.
     *
     * @var array
     */
    protected $done = array();

    /**
     * Reset done array.
     *
     * Using teardown rather than setup as this is less likely to get
     * accidentally overwritten!
     */
    function tearDownSuite()
    {
        $this->done = array();
    }

    /**
     * Child must supply the base URL for spidering.
     *
     * @return string  URL (fully qualified)
     */
    protected function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Sets the base URL.
     *
     * @param string $url  base URL
     * @return T_Unit_Spider  fluent
     */
    function setBaseUrl($url)
    {
        $this->base_url = $url;
        return $this;
    }

    /**
     * Whether the URL discovered should be tested.
     *
     * @param string $url  potential URL
     * @return bool
     */
    protected function isTestableUrl($url)
    {
        $base = rtrim($this->getBaseUrl(),'/').'/';
        return strpos($url,$base)===0; // page must be a sub-page of start
    }

    /**
     * Execute this test suite for each spidered URL.
     *
     * @return string
     */
    protected function executeTests($url=null)
    {
        if (is_null($url)) $url = $this->getBaseUrl();
        if (!$url) $this->skipAll('No root URL defined for spider');

        // execute tests on this URL
        $this->load($url);
        parent::executeTests();
        $this->done[$url] = true;

        // find all links in the HTML, guarding against the situation
        // where the page cannot be loaded as XML
        try {
            $links = $this->getElement('//@href');
        } catch (Exception $e) {
            // can't load as XML, so no more children to find
            return $this;
        }

        // iterate over them to continue spider.
        foreach ($links as $link) {
            $target = $this->getNormalisedUrl((string) $link,$url);
            if (!isset($this->done[$target]) && $this->isTestableUrl($target)) {
                $this->executeTests($target);
            }
        }

        return $this;
    }

    /**
     * Gets the absolute URL.
     *
     * @param string $target  relative/etc URL
     * @param string $base    base page url
     * @return string  normalised URL.
     */
    function getNormalisedUrl($target,$base)
    {
        $target = rtrim($target,' /'); // remove trailing slashes

        // expand relative links
        if (strpos($target,'://')===false) {
            $target = rtrim($base,'/').'/'.$target;
            // this doesn't handle <base> tags, or absolute document root
            // references like /css/xxx
        }

        // remove any anchors, etc.
        if (($pos=strpos($target,'#'))!==false) {
            $target = substr($target,0,$pos);
        }

        return $target;
    }

}
