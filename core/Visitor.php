<?php
/**
 * Contains the T_Visitor interface.
 *
 * This file contains the definition of the T_Visitor interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a Visitor object.
 *
 * This interface is applied to any object that is used as a Visitor. Most
 * visitors will be used in a function such as:
 * <code>
 * $method = 'visit'.array_pop(explode('_',get_class($this)));
 * $visitor->$method($this);
 * if ($this->isChildren()) {
 *     $visitor->preChildEvent();
 *     foreach ($this->children as $child) {
 *         $child->accept($visitor);
 *     }
 *     $visitor->postChildEvent();
 * }
 * </code>
 *
 * @see T_Visitorable
 * @package core
 */
interface T_Visitor
{

    /**
     * Pre-Child visitor event.
     *
     * This function is called prior to moving onto a sub-list of composite
     * children.
     */
    function preChildEvent();

    /**
     * Post-Child visitor event.
     *
     * This function is called after working through a sub-list of composite
     * children.
     */
    function postChildEvent();

    /**
     * Whether to traverse children.
     *
     * This function is called when visiting a composite that has children. It
     * is generally called before checking the composite and the retrun value is
     * a boolena that indicates whether the children of the composite should be
     * traversed. It's useful to prevent deep traversing through complex
     * composites, that may be partially lazy loaded, when just want to traverse
     * to a max depth.
     *
     * @return bool  whether to traverse the children.
     */
    function isTraverseChildren();

}