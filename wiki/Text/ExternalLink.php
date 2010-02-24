<?php
/**
 * Defines the T_Text_ExternalLink class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A link to an external resource.
 *
 * @package wiki
 */
class T_Text_ExternalLink extends T_Text_Plain
{

    /**
     * The external URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Create the external link.
     *
     * @param mixed $content  text content
     */
    function __construct($content,$url)
    {
        parent::__construct($content);
        $this->url = $url;
    }

    /**
     * Get the URL of the external resource.
     *
     * @param function $filter  optional filter
     * @return string
     */
    function getUrl($filter=null)
    {
        return _transform($this->url,$filter);
    }

    /**
     * Returns original formatted text.
     *
     * @return string  original formatting
     */
    function __toString()
    {
        $content = parent::__toString();
        return '['.$this->getUrl().' '.$content.']';
    }

}