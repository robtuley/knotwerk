<?php
/**
 * Contains the T_Test_VisitorStub class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A visitor stub used for testing visitorable interfaces.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_VisitorStub implements T_Visitor,T_Test_Stub
{

    /**
     * The depth in the current tree hierarchy.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * Whether to traverse children.
     *
     * @var bool
     */
    protected $traverse_children = true;

    /**
     * Array of visited args, methods and depth.
     *
     * @var array
     */
    protected $visited = array( 'object' => array(),
                                'method' => array(),
                                'depth'  => array()  );


    /**
     * Pre-Child visitor event.
     */
    function preChildEvent()
    {
        $this->depth++;
    }

    /**
     * Post-Child visitor event.
     */
    function postChildEvent()
    {
        $this->depth--;
    }

    /**
     * Whether to traverse children.
     *
     * @return bool  whether to traverse composite children.
     */
    function isTraverseChildren()
    {
        return $this->traverse_children;
    }

    /**
     * Set whether to traverse children.
     *
     * @param bool $onoff  whether to traverse children or not.
     */
    function setTraverseChildren($onoff)
    {
        $this->traverse_children = $onoff;
    }

    /**
     * Gets details of visited objects.
     *
     * Returns a nested array of visited object details. The first level of the
     * array has 3 elements: 'object' that stores a sub-array of the objected
     * visited; 'method' which stores a sub array of the method names used;
     * 'depth' that stores the depth they were visited at.
     *
     * @return array  visited object details.
     */
    function getVisited()
    {
        return $this->visited;
    }

    /**
     * Renders a node through default renederer.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        // check method call
        if (strcmp(substr($method,0,5),'visit')!==0 || count($arg)!=1) {
            throw new BadFunctionCallException("Cannot handle call to $method");
        }
        $node = $arg[0];
        // store visited objects
        $this->visited['object'][] = $node;
        $this->visited['method'][] = $method;
        $this->visited['depth'][] = $this->depth;
    }

}