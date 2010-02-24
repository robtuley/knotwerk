<?php
/**
 * Defines the T_Exception_Handler_Action interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for action taken on an exception.
 *
 * This interface encapsulates an exception action: i.e. something that should
 * done when an exception bubbles up to the global scope.
 *
 * @package core
 */
interface T_Exception_Handler_Action
{

    /**
     * Tries to handle the exception.
     *
     * @param Exception $e
     * @return bool  whether this action handled the exception OK
     */
    function handle(Exception $e);

}