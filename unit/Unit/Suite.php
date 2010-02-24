<?php
/**
 * Defines the class T_Unit_Suite.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Unit test suite.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
class T_Unit_Suite extends T_Unit_Case implements T_Composite
{

    /**
     * Children of current object.
     *
     * @var array
     */
    protected $children = array();

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
     * Add a child test case.
     *
     * @param T_CompositeLeaf $child  child test case.
     * @return T_Unit_Suite  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        foreach ($this->observers as $observe) {
            $child->attach($observe);
        }
        if (isset($key)) {
            $this->children[$key] = $child;
        } else {
            $this->children[] = $child;
        }
        return $this;
    }

    /**
     * Attach an observer to the test cases.
     *
     * @param T_Unit_Observer $observer  observer
     * @param bool $trigger  whether to trigger start/end
     * @return T_Unit_Case  fluent interface
     */
    function attach(T_Unit_Observer $observer,$trigger=true)
    {
        foreach ($this->children as $child) {
            $child->attach($observer,false);
             // do not initialise child as will do in parent
        }
        return parent::attach($observer,$trigger);
    }

    /**
     * Sets the factory.
     *
     * @param T_Unit_Factory $factory
     * @return T_Unit_Case  fluent
     */
    function setFactory($factory)
    {
        foreach ($this->children as $child) {
            $child->setFactory($factory);
        }
        return parent::setFactory($factory);
    }

    /**
     * Execute the unit test cases.
     *
     * @return T_Unit_Case  fluent interface
     */
    function execute()
    {
        $this->createDefaultFactory();
        foreach ($this->trigger as $observer) {
            $observer->init();
        }
        $this->setUpSuite();
        $this->executeTests();
        foreach ($this->children as $child) {
            $child->execute();
        }
        $this->tearDownSuite();
        foreach ($this->trigger as $observer) {
            $observer->complete();
        }
        return $this;
    }

}
