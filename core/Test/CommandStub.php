<?php
/**
 * Defines the T_Test_CommandStub class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Stub to use to test commands.
 *
 * @package coreTests
 */
class T_Test_CommandStub implements T_Command,T_Test_Stub
{

    /**
     * Whether command is executed.
     *
     * @var bool
     */
    protected $is_exe = false;

    /**
     * Context of command execution.
     *
     * @var mixed
     */
    protected $exe_context = null;

    /**
     * Whether command is
     *
     * @var unknown_type
     */
    protected $is_undo = false;

    /**
     * Context of command undo.
     *
     * @var mixed
     */
    protected $undo_context = null;

    /**
     * Execute command.
     */
    function execute($context)
    {
        $this->is_exe = true;
        $this->exe_context = $context;
    }

    /**
     * Undo command.
     */
    function undo($context)
    {
        $this->is_undo = true;
        $this->undo_context = $context;
    }

    /**
     * Whether the command has been executed.
     *
     * @return bool
     */
    function isExecute()
    {
        return $this->is_exe;
    }

    /**
     * Whether the command has been undone.
     *
     * @return bool
     */
    function isUndo()
    {
        return $this->is_undo;
    }

    /**
     * Gets the context of execution.
     *
     * @return mixed
     */
    function getExecuteContext()
    {
        return $this->exe_context;
    }

    /**
     * Gets the context of undo.
     *
     * @return mixed
     */
    function getUndoContext()
    {
        return $this->undo_context;
    }

}