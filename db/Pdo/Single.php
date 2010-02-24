<?php
/**
 * Contains the T_Pdo_Single class.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a single db connection (shared master/slave).
 *
 * @package db
 */
class T_Pdo_Single implements T_Db
{

    /**
     * Connection factory.
     *
     * @var T_Db_Factory
     */
    protected $factory;

    /**
     * Master connection.
     *
     * @var T_Pdo_Master
     */
    protected $master = false;

    /**
     * Slave connection.
     *
     * @var T_Pdo_Slave
     */
    protected $slave = false;

    /**
     * Create factory.
     *
     * @param T_Pdo_Connection $factory
     */
    function __construct(T_Pdo_Connection $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieves the master connection.
     *
     * @param string $context  optional namespace
     * @return T_Pdo_Master
     */
    function master($context=null)
    {
        if (false===$this->master) {
            $this->master = new T_Pdo_Master($this->factory->connect());
        }
        return $this->master;
    }

    /**
     * Retrieves the slave connection.
     *
     * @param string $context  optional namespace
     * @return T_Db_Slave
     */
    function slave($context=null)
    {
        if (false===$this->slave) {
            $this->slave = new T_Pdo_Slave($this->factory->connect());
        }
        return $this->slave;
    }

    /**
     * Closes all open connections.
     *
     * @return T_Db  fluent interface
     */
    function close()
    {
        $this->factory->close();
        $this->master = $this->slave = false;
        return $this;
    }

    /**
     * Gets the DB type name.
     *
     * @param function $filter  optional filter
     * @return string
     */
    function getName($filter=null)
    {
        return $this->factory->getName($filter);
    }

    /**
     * Whether the connection is of a particular type.
     *
     * @param $type int  e.g. T_Db::MYSQL
     * @return bool
     */
    function is($type)
    {
        return $this->factory->is($type);
    }

}
