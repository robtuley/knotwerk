<?php
/**
 * Contains the T_Unit_TerminalDisplay class.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Observer to display test results when executed in a terminal window.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
class T_Unit_TerminalDisplay implements T_Unit_Observer
{

    /**
     * Array of error, skip and count.
     *
     * @var array
     */
    protected $count;

    /**
     * Tracks failure messages.
     *
     * @var array
     */
    protected $msg;

    /**
     * Outputs a tracking character.
     *
     * @param string $type  tracking character
     * @return T_Unit_TerminalDisplay  fluent interface
     */
    protected function track($type)
    {
         if (array_sum($this->count)%70 == 0) echo EOL;
         echo $type;
         ++$this->count[$type];
         return $this;
    }

    /**
     * Stores a message.
     *
     * @param string $type
     * @param string $text
     * @param bool $unique  only add unique messages
     * @return T_Unit_TerminalDisplay  fluent interface
     */
    protected function msg($type,$text,$unique=false)
    {
        if (!$unique || !in_array($text,$this->msg[$type])) {
            $this->msg[$type][] = $text;
        }
        return $this;
    }

    /**
     * Get standard report.
     *
     * @param ReflectionMethod $method
     * @param Exception $error
     * @return string
     */
    protected function getReport(ReflectionMethod $method,Exception $error)
    {
        $report  = $error->getMessage();
        $report .= EOL.'Method: '.$method->getName().
                   EOL.'Location: '.$method->getFileName().', '.$method->getStartLine();
        return $report;
    }

    /**
     * Initialise test run.
     *
     * @return T_Unit_Observer  fluent interface
     */
    function init()
    {
        $this->count = array('E'=>0,'S'=>0,'F'=>0,'.'=>0);
        $this->msg = array('E'=>array(),'S'=>array(),'F'=>array());
    }

    /**
     * Complete test run.
     *
     * @return T_Unit_Observer  fluent interface
     */
    function complete()
    {
        // summary
        echo EOL.EOL.array_sum($this->count).' tests completed.';
        echo EOL.'('.$this->count['.'].' passed, '.$this->count['S'].' skipped, '.
                $this->count['F'].' failed, '.$this->count['E'].' errors)';
        // errors
        if ($this->count['E']>0) {
            echo EOL.EOL.$this->count['E'].' errors occurred:';
            foreach ($this->msg['E'] as $key => $msg) {
                echo EOL.EOL.($key+1).'. '.$msg;
            }
        }
        // fails
        if ($this->count['F']>0) {
            echo EOL.EOL.$this->count['F'].' failures occurred:';
            foreach ($this->msg['F'] as $key => $msg) {
                echo EOL.EOL.($key+1).'. '.$msg;
            }
        }
        // skipped
        if ($this->count['S']>0) {
            echo EOL.EOL.$this->count['S'].' tests were skipped, due to:';
            foreach ($this->msg['S'] as $key => $msg) {
                echo EOL.($key+1).'. '.$msg;
            }
        }
        echo EOL.EOL;
    }

    /**
     * Register a test error.
     *
     * @param Exception $error
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function error(Exception $error,ReflectionMethod $method)
    {
        $report = $this->getReport($method,$error).EOL.$error->__toString();
        $this->track('E')
             ->msg('E',$report);
    }

    /**
     * Register a test failure.
     *
     * @param T_Exception_TestFail $fail
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function fail(T_Exception_TestFail $fail,ReflectionMethod $method)
    {
        $this->track('F')
             ->msg('F',$this->getReport($method,$fail));
    }

    /**
     * Register a test skip.
     *
     * @param T_Exception_TestSkip $skip
     * @param @param ReflectionMethod|ReflectionClass $method_or_class
     * @return T_Unit_Observer  fluent interface
     */
    function skip(Exception $skip,$method_or_class)
    {
        $this->track('S')
             ->msg('S',$skip->getMessage(),true);
                                        // ^ make unique
    }

    /**
     * Register a test pass.
     *
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function pass(ReflectionMethod $method)
    {
        $this->track('.');
    }

}
