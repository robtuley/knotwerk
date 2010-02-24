<?php
/**
 * Defines the T_Exception_Handler_Debug interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Debug exception handler.
 *
 * @package core
 */
class T_Exception_Handler_Debug implements T_Exception_Handler_Action
{

    /**
     * Level of error reporting.
     *
     * @var int
     */
    protected $level;

    /**
     * Create debug error handler.
     *
     * @param int $level
     */
    function __construct($level)
    {
        $this->level = $level;
        error_reporting($level);
        ini_set('display_errors',1);
    }

    /**
     * Tries to handle the exception.
     *
     * @param Exception $e
     * @return true
     */
    function handle(Exception $e)
    {
        if (!($e instanceof ErrorException) || $e->getSeverity()&$this->level) {
            echo '<pre>',$e,'</pre>';
            $this->doExit();
        }
        return false;
    }

    /**
     * Exit code.
     */
    protected function doExit()
    {
        exit();
    }

}
