<?php
/**
 * Contains the T_View_Composite class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A View Composite that can be used to groups views together.
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_View_Composite implements T_Composite,T_View
{

    /**
     * Child views.
     *
     * @var array
     */
    protected $children = array();

    /**
     * Whether the composite has any children.
     *
     * @return bool  whether there are any children
     */
    function isChildren()
    {
        return count($this->children)>0;
    }

    /**
     * Gets the available composite object (self in this case).
     *
     * @return T_Composite  self composite
     */
    function getComposite()
    {
        return $this;
    }

    /**
     * Add a child Compsite.
     *
     * @param T_CompositeLeaf $child  child URL object.
     * @return T_View_Composite  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        if (isset($key)) {
            $this->children[$key] = $child;
        } else {
            $this->children[] = $child;
        }
        return $this;
    }

    /**
     * Allows sub-portions of composite tree to be accessed by keywords.
     *
     * @param string $key  child keyword
     * @return OKT_LeafComposite  the tree sub-portion
     */
    function __get($key)
    {
        if (array_key_exists($key,$this->children)) {
            return $this->children[$key];
        } else {
            throw new InvalidArgumentException("child $key doesn't exist");
        }
    }

    /**
     * Render composite content.
     *
     * @return string  stub content
     */
    function __toString()
    {
        $content = '';
        foreach ($this->children as $child) {
            $content .= $child->__toString();
        }
        return $content;
    }

    /**
     * Render composite content to buffer.
     *
     * @return T_View_Composite  fluent interface
     */
    function toBuffer()
    {
        foreach ($this->children as $child) {
            $child->toBuffer();
        }
        return $this;
    }

}