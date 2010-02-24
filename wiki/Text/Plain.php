<?php
/**
 * Defines the T_Text_Plain class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Formatted text broken ready to be rendered.
 *
 * @package wiki
 * @license http://knotwerk.com/licence MIT
 */
class T_Text_Plain extends T_Text_Composite implements T_Text_Parseable
{

    /**
     * Text content.
     *
     * @var string
     */
    protected $content;

    /**
     * Create the formatted text.
     *
     * @param mixed $content  text content
     */
    function __construct($content=null)
    {
        $this->content = $content;
    }

    /**
     * Get the content.
     *
     * @param function $filter  optional output filter
     * @return string  content
     */
    function getContent($filter=null)
    {
        return _transform($this->content,$filter);
    }

    /**
     * Set content.
     *
     * @param string $content  new content
     * @return OKT_FormattedString  fluent interface
     */
    function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Returns original formatted full text.
     */
    function __toString()
    {
        $str = $this->getContent();
        $str .= parent::__toString();
        return $str;
    }

}
