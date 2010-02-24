<?php
/**
 * Contains the T_Unit_Observer interface.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a unit test observer object.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
interface T_Unit_Observer
{

    /**
     * Initialise test run.
     *
     * @return T_Unit_Observer  fluent interface
     */
    function init();

    /**
     * Complete test run.
     *
     * @return T_Unit_Observer  fluent interface
     */
    function complete();

    /**
     * Register a test error.
     *
     * @param Exception $error
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function error(Exception $error,ReflectionMethod $method);

    /**
     * Register a test failure.
     *
     * @param T_Exception_TestFail $fail
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function fail(T_Exception_TestFail $fail,ReflectionMethod $method);

    /**
     * Register a test skip.
     *
     * @param T_Exception_TestSkip $skip
     * @param ReflectionMethod|ReflectionClass $method_or_class
     * @return T_Unit_Observer  fluent interface
     */
    function skip(Exception $skip,$method_or_class);

    /**
     * Register a test pass.
     *
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function pass(ReflectionMethod $method);

}
