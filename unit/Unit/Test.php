<?php
/**
 * Contains the T_Unit_Test interface.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a unit test object.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
interface T_Unit_Test
{

    /**
     * Executes the unit tests.
     *
     * @return T_Unit_Test  fluent interface
     */
    function execute();

    /**
     * Attach an observer to the test cases.
     *
     * @param T_Unit_Observer $observer  observer
     * @param bool $trigger  whether to trigger start/end events
     * @return T_Unit_Test  fluent interface
     */
    function attach(T_Unit_Observer $observer,$trigger=true);

}