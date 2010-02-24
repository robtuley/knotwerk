<?php
/**
 * Contains the class T_Mysql_Factory.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Mysql connection factory.
 *
 * @package db
 */
class T_Mysql_Connection extends T_Pdo_Connection
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
        parent::__construct("mysql:host=$host;dbname=$db_name".
                            (!is_null($port) ? ";port=$port" : ''),
                            $user,$passwd);
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
            $conn->exec("SET CHARACTER SET utf8, time_zone='+0:00'");
           // The connection character set must be consistent with the lib (utf8).
           // MySQL also supports the concept of timezones, and although they do not
           // have a affect on stored values, they do affect the value of NOW()
           // and extraction of UNIX_TIMESTAMPS(__). All data passed to the db should
           // be in UTC (i.e. GMT).
           //
           // the most efficient way of doing this is probably to use a last
           // argument to the parent constructor of
           //     array(PDO::MYSQL_ATTR_INIT_COMMAND => $sql)
           // However, a PHP5.3 bug means this constant is not always defined, so for
           // compatibility we issue the query separately.
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
        return _transform("MySQL",$filter);
    }

}
