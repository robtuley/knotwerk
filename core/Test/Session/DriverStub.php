<?php
/**
 * Defines the T_Test_Session_DriverStub class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Class for a test session driver.
 *
 * @package core
 */
class T_Test_Session_DriverStub implements T_Session_Driver,T_Test_Stub
{

    /**
     * Data.
     *
     * @var array
     */
    protected $data;

    /**
     * Whether driver is regenerated.
     *
     * @var bool
     */
    protected $regenerated = false;

    /**
     * Whether the driver is destroyed.
     *
     * @var unknown_type
     */
    protected $destroyed = false;

    /**
     * Create driver.
     *
     * @param array $data
     */
    function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Saves data.
     *
     * @param mixed $data  data
     * @return T_Session_Driver  fluent interface
     */
    function save($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Retrieves data.
     *
     * @return array  data
     */
    function get()
    {
        return $this->data;
    }

    /**
     * Gets the session data.
     *
     * @return array
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * Attach a driver filter.
     *
     * @param T_Filter_Reversable $filter
     * @return T_Session_Driver  fluent interface
     */
    function attachFilter(T_Filter_Reversable $filter)
    {
        return $this;
    }

    /**
     * Regenerates session driver.
     *
     * @return T_Session_Driver  fluent interface
     */
    function regenerate()
    {
        $this->regenerated = true;
        return $this;
    }

    /**
     * Whether driver is regenerated.
     *
     * @return bool
     */
    function isRegenerated()
    {
        return $this->regenerated;
    }

    /**
     * Destroys session.
     *
     * @return T_Session_Driver  fluent interface
     */
    function destroy()
    {
        $this->destroyed = true;
        return $this;
    }

    /**
     * Whether driver is destroyed.
     *
     * @return bool
     */
    function isDestroyed()
    {
        return $this->destroyed;
    }

}