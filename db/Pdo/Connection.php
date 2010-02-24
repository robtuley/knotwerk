<?php
/**
 * Contains the class T_Pdo_Connection.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * PDO connection factory.
 *
 * @package db
 */
class T_Pdo_Connection implements T_Db_Factory
{

    /**
     * DSN.
     *
     * @var string
     */
    protected $dsn;

    /**
     * Username.
     *
     * @var string
     */
    protected $username;

    /**
     * Password.
     *
     * @var string
     */
    protected $password;

    /**
     * Array of driver options.
     *
     * @var array
     */
    protected $options;

    /**
     * Connection.
     *
     * @var PDO
     */
    protected $conn = null;

    /**
     * Create PDO connection.
     *
     * @param string $file  filename
     * @param int $mode   mode
     */
    function __construct($dsn,$username=null,$password=null,$options=array())
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;
    }

    /**
     * Get connection.
     *
     * @return PDO
     * @throws T_Exception_Db  on connection failure
     */
    function connect($context=null)
    {
        if (is_null($this->conn)) {
            try {
                $this->conn = new PDO($this->dsn,
                                      $this->username,
                                      $this->password,
                                      $this->options);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE,
                                          PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_ORACLE_NULLS,
                                          PDO::NULL_EMPTY_STRING);
            } catch (PDOException $e) {
                throw new T_Exception_Db($e->getMessage());
            }
        }
        return $this->conn;
    }

    /**
     * Closes the DB connection.
     *
     * @param string $context  optional namespace context
     * @return T_Pdo_Connection  fluent interface
     */
    function close($context=null)
    {
        $this->conn = null; // no methods available to explicitally close conn
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
        return _transform("PDO:$this->dsn",$filter);
    }

    /**
     * Whether the connection is of a particular type.
     *
     * @param $type int  e.g. T_Db::MYSQL
     * @return bool
     */
    function is($type)
    {
        return ($type===T_Db::POSTGRES && strncmp('pgsql:',$this->dsn,6)===0) ||
               ($type===T_Db::MYSQL && strncmp('mysql:',$this->dsn,6)===0) ||
               ($type===T_Db::SQLITE && strncmp('sqlite:',$this->dsn,7)===0);
    }

}
