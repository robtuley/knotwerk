<?php
/**
 * Contains the T_Text_Element interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a wiki syntax element (paragraph, etc).
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
interface T_Text_Element extends T_CompositeLeaf,T_Visitorable
{

    /**
     * Sets the parent.
     *
     * @param string $classname
     */
    function setParent($classname);

    /**
     * Whether the current object has a particular class as its parent.
     *
     * @param string $classname
     * @return bool
     */
    function isContainedBy($classname);

    /**
     * Returns original formatted full text.
     *
     * @return string
     */
    function __toString();

}