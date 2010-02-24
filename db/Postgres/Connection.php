<?php
/**
 * Contains the class T_Postgres_Connection.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * PostgreSQL connection factory.
 *
 * @package db
 */
class T_Postgres_Connection extends T_Pdo_Connection
{

    /**
     * Create connection.
     *
     * @param string $host  database host (e.g. db.example.com)
     * @param string $user  database username
     * @param string $passwd  database password for user
     * @param string $db_name  database name
     * @param int $port  port number
     */
    function __construct($host,$user,$passwd,$db_name,$port=null)
    {
        parent::__construct("pgsql:host=$host;dbname=$db_name".
                            (!is_null($port) ? ";port=$port" : '').
                            ";user=$user;password=$passwd");
    }

    /**
     * Get connection.
     *
     * @return PDO
     * @throws T_Exception_Db  on connection failure
     */
    function connect($context=null)
    {
        $init = is_null($this->conn);
        $conn = parent::connect($context);
        if ($init) {
            $conn->exec("SET CLIENT_ENCODING TO 'utf8'");
            $conn->exec("SET TIME ZONE '+0:00'");
        }
        return $conn;
    }

    /**
     * Gets the DB type name.
     *
     * @param function $filter  optional filter
     * @return string
     */
    function getName($filter=null)
    {
        return _transform("PostgreSQL",$filter);
    }

    /**
     * Quotes a string as a database label (tablename,field).
     *
     * @param $value string  label
     * @return string  quoted label
     */
    function label($value)
    {
        return '"'.$value.'"';
    }

}
