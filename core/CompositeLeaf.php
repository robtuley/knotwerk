<?php
/**
 * Contains the T_CompositeLeaf interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a composite leaf object.
 *
 * This interface is applied to any object that follows a composite design
 * pattern. It is applied to 'leaf' objects - those objects that can be part of
 * a bigger composite whole, but cannot have any children of their own.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
interface T_CompositeLeaf
{

    /**
     * Returns the composite object or null.
     *
     * This returns a composite object (that has the T_Composite API) or if
     * the object is just a leaf, it returns null.
     *
     * @return T_Composite  composite object (self or null)
     */
    function getComposite();

}