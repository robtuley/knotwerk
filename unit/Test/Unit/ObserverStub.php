<?php
/**
 * Contains the T_Test_Unit_ObserverStub class.
 *
 * @package unitTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Stub observer to display test results when executed in a terminal window.
 *
 * @package unitTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Unit_ObserverStub implements T_Unit_Observer,T_Test_Stub
{

    protected $log = array();
    protected $is_init = false;
    protected $is_complete = false;

    function init()
    {
        $this->is_init = true;
    }

    function isInit()
    {
        return $this->is_init;
    }

    function complete()
    {
        $this->is_complete = true;
    }

    function isComplete()
    {
        return $this->is_complete;
    }

    function error(Exception $error,ReflectionMethod $method)
    {
        $this->log[] = array('method'=>'error',
                             'args'  => array($method,$error) );
    }

    function fail(T_Exception_TestFail $fail,ReflectionMethod $method)
    {
        $this->log[] = array('method'=>'fail',
                             'args'  => array($method,$fail) );
    }

    function skip(Exception $skip,$method)
    {
        $this->log[] = array('method'=>'skip',
                             'args'  => array($method,$skip) );
    }

    function pass(ReflectionMethod $method)
    {
        $this->log[] = array('method'=>'pass',
                             'args'  => array($method) );
    }

    function getLog()
    {
        return $this->log;
    }

}
