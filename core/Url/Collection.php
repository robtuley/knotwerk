<?php
/**
 * Contains the T_Url_Collection class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An XHTML URL Composite.
 *
 * This class encapsulates an XHTML URL to a different resource and allows for
 * a sub-tree hierarchy of similar URL objects to be connected with it. This
 * makes building sitemaps or other link lists easier.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Url_Collection extends T_Url_Leaf
                       implements T_Composite,T_Visitorable,Iterator
{

    /**
     * Children of current object.
     *
     * @var array
     */
    protected $children = array();

    /**
     * Iterator next component validity.
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * Whether the current object has any children.
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
     * Gets the first active child.
     *
     * @return T_Url_Leaf|false  active child
     */
    function getActiveChild()
    {
        foreach ($this->children as $child) {
        	if ($child->isActive()) return $child;
        }
        return false;
    }

    /**
     * Add a child URL.
     *
     * @param T_CompositeLeaf $child  child URL object.
     * @return T_Url_Collection  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        $child->setParent($this);
        if (isset($key)) {
            $this->children[$key] = $child;
        } else {
            $this->children[] = $child;
        }
        if ($child->isActive()) {
            $this->setActive();
        }
        return $this;
    }

    /**
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     */
    function accept(T_Visitor $visitor)
    {
        $name   = explode('_',get_class($this));
            // remove prefix (first in array), and concatenate others
        array_shift($name);
        $method = 'visit'.implode('',$name);
        $visitor->$method($this);
        if ($visitor->isTraverseChildren() && $this->isChildren()) {
            $visitor->preChildEvent();
            foreach ($this->children as $child) {
        	   $child->accept($visitor);
            }
            $visitor->postChildEvent();
        }
    }

    /**
     * Allows sub-portions of composite tree to be accessed by keywords.
     *
     * @param string $key  child keyword
     * @return OKT_HttpUrl  the tree sub-portion
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
     * Allows isset tests to see if sub-portions of tree are available.
     *
     * @param string $key  child keyword
     * @return OKT_HttpUrl  the tree sub-portion
     */
    function __isset($key)
    {
        return isset($this->children[$key]);
    }

    /**
     * Rewind array.
     */
    function rewind()
    {
        $this->valid = (false !== reset($this->children));
    }

    /**
     * Return the current child element.
     *
     * @return T_CompositeLeaf  the current array element
     */
    function current()
    {
        return current($this->children);
    }

    /**
     * Return the key of the current array element.
     *
     * @return int  current element key
     */
    function key()
    {
        return key($this->children);
    }

    /**
     * Move forward by one in array.
     */
    function next()
    {
        $this->valid = (false !== next($this->children));
    }

    /**
     * Validity of current element.
     *
     * @return bool  validity of the next element
     */
    function valid()
    {
        return $this->valid;
    }

}