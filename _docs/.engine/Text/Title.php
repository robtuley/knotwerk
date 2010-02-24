<?php
/**
 * Contains the Doc_FormattedTextTitleVisitor class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Extracts first highest level header from formatted text.
 *
 * @package docs
 */
class Text_Title implements T_Visitor
{

    /**
     * Title.
     *
     * @var string
     */
    protected $title = false;

    /**
     * Title level.
     *
     * @var int
     */
    protected $level = 7;

    /**
     * Ignores all non-header visit calls.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg) { }

    /**
     * Visit a header node.
     *
     * @param T_Text_Header $node
     */
    function visitTextHeader(T_Text_Header $node)
    {
        $level = $node->getLevel();
        if ($this->title===false || $this->level>$level) {
            $this->title = $node->getContent();
            $this->level = $level;
        }
    }


    /**
     * Pre-Child visitor event.
     */
    function preChildEvent() { }

    /**
     * Post-Child visitor event.
     */
    function postChildEvent() { }

    /**
     * Always traverse children.
     *
     * @return bool  whether to traverse composite children.
     */
    function isTraverseChildren()
    {
        return true;
    }

    /**
     * Return XHTML string.
     *
     * @return string
     */
    function __toString()
    {
        return $this->title;
    }

}
