<?php
/**
 * Contains the T_Unit_XmlLog class.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Observer to log unit test history to XML file.
 *
 * @package unit
 */
class T_Unit_XmlLog implements T_Unit_Observer
{

    /**
     * Counts of pass, fail, etc.
     *
     * @var int
     */
    protected $passed;
    protected $failed;
    protected $errored;
    protected $skipped;

    /**
     * XML file path.
     *
     * @var string
     */
    protected $path;

    /**
     * Create observer based on XML path.
     *
     * @param string $path  path to XML file
     */
    function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Zero counts.
     *
     * @return T_Unit_XmlLog
     */
    function init()
    {
        $this->passed=$this->failed=$this->errored=$this->skipped=0;
        return $this;
    }

    /**
     * Prepend to log once completed.
     *
     * @return T_Unit_XmlLog
     */
    function complete()
    {
        if (file_exists($this->path)) {
            $sxe = simplexml_load_file($this->path);
        } else {
            $sxe = simplexml_load_string('<log></log>');
        }
        $test = $sxe->addChild('test');
        $test['date'] = time();
        $test->addChild('passed',$this->passed);
        $test->addChild('failed',$this->failed);
        $test->addChild('errored',$this->errored);
        $test->addChild('skipped',$this->skipped);
        file_put_contents($this->path,$sxe->asXml());
        return $this;
    }

    /**
     * Count a test error.
     *
     * @param Exception $error
     * @param ReflectionMethod $method
     * @return T_Unit_XmlLog
     */
    function error(Exception $error,ReflectionMethod $method)
    {
       ++$this->errored;
       return $this;
    }

    /**
     * Register a test failure.
     *
     * @param T_Exception_TestFail $fail
     * @param ReflectionMethod $method
     * @return T_Unit_XmlLog
     */
    function fail(T_Exception_TestFail $fail,ReflectionMethod $method)
    {
        ++$this->failed;
       return $this;
    }

    /**
     * Register a test skip.
     *
     * @param T_Exception_TestSkip $skip
     * @param ReflectionMethod|ReflectionClass $method_or_class
     * @return T_Unit_XmlLog
     */
    function skip(Exception $skip,$method_or_class)
    {
        ++$this->skipped;
       return $this;
    }

    /**
     * Register a test pass.
     *
     * @param ReflectionMethod $method
     * @return T_Unit_XmlLog
     */
    function pass(ReflectionMethod $method)
    {
        ++$this->passed;
       return $this;
    }

}
