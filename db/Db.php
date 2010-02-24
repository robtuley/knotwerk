<?php
/**
 * Contains the T_Db interface.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a DB object.
 *
 * @package db
 */
interface T_Db
{

    /**
     * Identity constants.
     */
    const MYSQL=1,
          SQLITE=2,
          POSTGRES=3;

    /**
     * Retrieves the master connection.
     *
     * @param string $context  optional namespace
     * @return T_Db_Master
     */
    function master($context=null);

    /**
     * Retrieves the slave connection.
     *
     * @param string $context  optional namespace
     * @return T_Db_Slave
     */
    function slave($context=null);

    /**
     * Closes all open connections.
     *
     * @return T_Db  fluent interface
     */
    function close();

    /**
     * Gets the DB signature.
     *
     * @return string
     */
    function getName();

    /**
     * Whether the connection is of a particular type.
     *
     * @param $type int  e.g. T_Db::MYSQL
     * @return bool
     */
    function is($type);

}
