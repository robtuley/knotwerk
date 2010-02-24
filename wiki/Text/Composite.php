<?php
/**
 * Defines the abstract class T_Text_Composite class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A wiki element composite.
 *
 * @package wiki
 */
abstract class T_Text_Composite extends T_Text_CompositeLeaf implements T_Composite,Iterator
{

    /**
     * Whether current iterator point is valid.
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * Composite children.
     *
     * @var array
     */
    protected $children = array();

    /**
     * Gets the available composite object.
     *
     * @return T_Text_Composite  self composite
     */
    function getComposite()
    {
        return $this;
    }

    /**
     * Add a child Composite.
     *
     * @param T_CompositeLeaf $child  child URL object.
     * @return T_Text_Composite  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        if (isset($key)) {
            $this->children[$key] = $child;
        } else {
            $this->children[] = $child;
        }
        $child->setParent(get_class($this));
        foreach (array_keys($this->parents) as $parent) {
            $child->setParent($parent);
        }
        return $this;
    }

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
     * Returns original formatted full text.
     */
    function __toString()
    {
        $str = '';
        foreach ($this as $child) {
        	$str .= $child->__toString();
        }
        return $str;
    }

    /**
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     */
    function accept(T_Visitor $visitor)
    {
        $name = explode('_',get_class($this));
        array_shift($name); // remove prefix
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
     * Rewind.
     */
    function rewind()
    {
        $this->valid = (false !== reset($this->children));
    }

    /**
     * Return the current array element.
     *
     * @return mixed  the current array element
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
