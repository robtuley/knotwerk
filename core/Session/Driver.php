<?php
/**
 * Defines the T_Session_Driver interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for session drivers.
 *
 * Session drivers are responsible for storing and retrieving the actual
 * session data. In order to prevent session corruption they should implement
 * some sort of locking strategy to serialize requests from the same session.
 *
 * @package core
 */
interface T_Session_Driver
{

    /**
     * Saves data.
     *
     * @param mixed $data  data
     * @return T_Session_Driver  fluent interface
     */
    function save($data);

    /**
     * Retrieves data.
     *
     * @return array  data
     */
    function get();

    /**
     * Attach a driver filter.
     *
     * @param T_Filter_Reversable $filter
     * @return T_Session_Driver  fluent interface
     */
    function attachFilter(T_Filter_Reversable $filter);

    /**
     * Regenerates session driver.
     *
     * @return T_Session_Driver  fluent interface
     */
    function regenerate();

    /**
     * Destroys session.
     *
     * @return T_Session_Driver  fluent interface
     */
    function destroy();

}