<?php
/**
 * Contains the T_Template_Composite class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A composite for template objects and other views.
 *
 * This composite performs the same function as the T_View_Composite class, but
 * it maintains the links necessary for templates to inherit parent helpers.
 *
 * @package views
 */
class T_Template_Composite extends T_Template_File implements T_Composite
{

    /**
     * Setup template composite.
     *
     * A template composite is simply an empty container that can contain any
     * number of sub-template objects. No arguments are required in the
     * constructor.
     */
    function __construct() { }

    /**
     * Whether the composite has any children.
     *
     * @return bool  whether there are any children
     */
    function isChildren()
    {
        return count($this->_children)>0;
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
     * Add a child.
     *
     * @param T_CompositeLeaf $child  child URL object
     * @param string $key  optional reference key
     * @return T_Template_Composite  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        if ($child instanceof T_Template_File) {
            $child->addParent($this);
        }
        if ($key) {
            $this->_children[$key] = $child;
        } else {
            $this->_children[] = $child;
        }
        return $this;
    }

    /**
     * Remove a child.
     *
     * @param string $key  child to remove
     * @return T_Template_Composite  fluent interface
     */
    function removeChild($key)
    {
        unset($this->_children[$key]);
        return $this;
    }

    /**
     * Prepend a child to first position.
     *
     * @param T_CompositeLeaf $child  child URL object
     * @param string $key  optional reference key
     * @return T_Template_Composite  fluent interface
     */
    function prependChild(T_CompositeLeaf $child,$key=null)
    {
        if ($child instanceof T_Template_File) {
            $child->addParent($this);
        }
        if ($key) {
            $this->_children = array($key=>$child)+$this->_children;
        } else {
            array_unshift($this->_children,$child);
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
        if (array_key_exists($key,$this->_children)) {
            return $this->_children[$key];
        } else {
            throw new InvalidArgumentException("child $key doesn't exist");
        }
    }

    /**
     * Disable direct addition of attributes.
     *
     * @param mixed $value  attribute value
     * @param string $name  attribute name
     * @throws BadFunctionCallException  when an attribute is set directly
     */
    function __set($name,$value)
    {
        throw new BadMethodCallException('attribute set disabled.');
    }

    /**
     * Render composite content.
     *
     * @return string  stub content
     */
    function __toString()
    {
        try {
            $content = '';
            foreach ($this->_children as $child) {
                $content .= $child->__toString();
            }
            return $content;
        } catch (Exception $e) {
            // exceptions cannot be thrown in __toString magic methods as
            // the PHP compiler cannot recover. Trigger error instead.
            $msg = "{$e->getMessage()}, line {$e->getLine()}, file {$e->getFile()}";
            trigger_error($msg,E_USER_ERROR);
        }
    }

    /**
     * Render composite content to buffer.
     *
     * @return T_Template_Composite  fluent interface
     */
    function toBuffer()
    {
        foreach ($this->_children as $child) {
            $child->toBuffer();
        }
        return $this;
    }

}
