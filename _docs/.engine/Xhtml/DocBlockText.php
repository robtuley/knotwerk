<?php
/**
 * Contains the T_Xhtml_DocBlockText class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Parses docblock text into XHTML content.
 *
 * @package docs
 */
class Xhtml_DocBlockText extends T_Filter_Skeleton
{

    protected $root;

    /**
     * Create doc block text filter.
     *
     * @param function $filter
     */
    function __construct(T_Url $root,$filter=null)
    {
        $this->root = $root;
        $filter = new Filter_AsFormattedText($filter);
        parent::__construct($filter);
    }

    /**
     * Converts docblock text to formatted text.
     *
     * @param string $value  content string
     * @return string  XHTML
     */
    protected function doTransform($value)
    {
        $visitor = new Xhtml_Text($this->root);
        $value->accept($visitor);
        return $visitor->__toString();
    }

}
