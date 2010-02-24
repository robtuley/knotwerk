<?php
/**
 * Contains the T_Command interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for an encapsulated command.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
interface T_Command
{

    /**
     * Executes the command, based on the context.
     *
     * @param mixed $context  context of the command
     * @return mixed
     */
    function execute($context);

    /**
     * Undo the executed command.
     *
     * @param mixed $context  context of the command
     * @return mixed
     */
    function undo($context);

}