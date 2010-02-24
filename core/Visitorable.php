<?php
/**
 * Contains the T_Visitorable interface.
 *
 * This file contains the definition of the T_Visitorable interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a object that can be visited (e.g. a composite).
 *
 * This interface is applied to any object that can be visited by another
 * object.
 *
 * @see T_Visitor
 * @package core
 */
interface T_Visitorable
{

    /**
     * Accepts and executes the visitor.
     *
     * @param T_Visitor  visitor to execute
     */
    function accept(T_Visitor $visitor);

}