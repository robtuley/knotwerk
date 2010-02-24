<?php
/**
 * Contains the T_Test_CompositeStub class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An composite that can be used for testing purposes.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_CompositeStub implements T_Composite,T_Visitorable,T_Test_Stub
{

    /**
     * Children of current object.
     *
     * @var array
     */
    protected $children = array();

    /**
     * Content to output.
     *
     * @var string
     */
    protected $content;

    /**
     * Create stub with (optional) content.
     *
     * @param string $content
     */
    function __construct($content=null)
    {
        $this->content = $content;
    }

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
     * Add a child Compsite.
     *
     * @param T_CompositeLeaf $child  child composite (leaf).
     * @return T_Test_CompositeStub  fluent interface
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
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     */
    function accept(T_Visitor $visitor)
    {
        $name   = explode('_',get_class($this));
        $method = 'visit'.array_pop($name);
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
     * @return T_Composite  the tree sub-portion
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
     * Set content of stub.
     *
     * @param string $value  content of stub.
     */
    function setContent($value)
    {
        $this->content = $content;
    }

    /**
     * Render stub content.
     *
     * @return string  stub content
     */
    function __toString()
    {
        return $this->content;
    }

    /**
     * Render stub content to buffer.
     *
     * @return string  stub content
     */
    function toBuffer()
    {
        echo $this->content;
        return $this;
    }

}