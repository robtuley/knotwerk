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
abstract class T_Text_CompositeLeaf implements T_Text_Element
{

    /**
     * Parents.
     *
     * @var array
     */
    protected $parents = array();

    /**
     * Sets the parent.
     *
     * @param string $classname
     */
    function setParent($classname)
    {
        if (!isset($this->parents[$classname])) $this->parents[$classname] = 0;
        ++$this->parents[$classname];
           // note that T_Text_List replies on this parent count to render original text
        return $this;
    }

    /**
     * Whether the current object has a particular class as its parent.
     *
     * @param string $classname
     * @return bool
     */
    function isContainedBy($classname)
    {
        return isset($this->parents[$classname]);
    }

    /**
     * Gets the available composite object.
     *
     * @return T_Text_Composite  self composite
     */
    function getComposite()
    {
        return null;
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
        return $this;
    }

}