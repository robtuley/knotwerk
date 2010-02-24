<?php
/**
 * Contains the T_Composite interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a composite object.
 *
 * This interface is applied to any object that follows a composite design
 * pattern and provides a concrete common method naming.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
interface T_Composite extends T_CompositeLeaf
{

    /**
     * Adds a child to the composite.
     *
     * The $key argument specifies an explicit string key that can be used to
     * access the child via __get() call.
     *
     * @param T_CompositeLeaf $child  child to add
     * @param string $key  optional key to refer to composite.
     * @return T_Composite  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null);

    /**
     * Whether the composite has any children.
     *
     * @return bool  whether the composite has any children
     */
    function isChildren();

}