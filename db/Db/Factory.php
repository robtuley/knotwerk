<?php
/**
 * Contains the T_Db_Factory interface.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for raw db connection object factory.
 *
 * @package db
 */
interface T_Db_Factory
{

    /**
     * Retrieves a raw db connection.
     *
     * @param string $context  optional namespace context
     * @return mixed
     */
    function connect($context=null);

    /**
     * Closes the DB connection.
     *
     * @param string $context  optional namespace context
     * @return T_Db_Factory  fluent interface
     */
    function close($context=null);

    /**
     * Gets the DB type name.
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
