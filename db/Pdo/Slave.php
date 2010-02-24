<?php
/**
 * Contains the class T_Pdo_Slave.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * PDO Slave.
 *
 * @package db
 */
class T_Pdo_Slave implements T_Db_Slave
{

    /**
     * PDO object.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Prepared statement cache.
     *
     * @var PDOStatement[]
     */
    protected $cache = array();

    /**
     * Creates the slave wrapper round a PDO connection.
     *
     * @param PDO $pdo  database connection
     */
    function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Executes a select query.
     *
     * @param string $sql  SQL to execute
     * @param array  $bind  values to bind into array
     * @return T_Pdo_Result|T_Pdo_Slave  result, or fluent interface write
     */
    function query($sql,array $bind=array())
    {
        try {
            if (count($bind)==0) {
                $stmt = $this->pdo->query($sql);
            } else {
                $stmt = $this->getStmt($sql);
                $stmt->execute($bind);
            }
            if ($stmt->columnCount()==0) {
                // write query..
                if (!$this->isWritePermitted()) {
                    $msg = "$sql is a WRITE query, not permitted on slave dbs";
                    throw new T_Exception_Query($this,$msg);
                }
                return $this; // fluent with a write query
            }
            return new T_Pdo_Result($stmt);
        } catch (PDOException $e) {
            throw new T_Exception_Query($this,$e->getMessage());
        }
    }

    /**
     * No write permitted on slaves.
     *
     * @return bool
     */
    protected function isWritePermitted()
    {
        return false;
    }

    /**
     * Executes a query and returns a single value.
     *
     * @param string $sql
     * @param array  $bind  values to bind into array
     * @return mixed  scalar value that has been queried
     */
    function queryAndFetch($sql,array $bind=array())
    {
        $data = $this->query($sql,$bind)->fetchAll();
        if (count($data)!=1) {
            throw new T_Exception_Query($this,"$sql does not return single row");
        }
        $row = current($data);
        return count($row)==1 ? current($row) : $row;
    }

    /**
     * Quotes a value for insertion into a query.
     *
     * @param mixed $value
     * @return string
     */
    function transform($value)
    {
        if (strlen($value)==0) return 'NULL';
        return $this->pdo->quote($value);
    }

    /**
     * Get PDO prepared statement.
     *
     * @param string $sql  SQL
     * @return PDOStatement
     */
    protected function getStmt($sql)
    {
        $key = md5($sql);
        if ($this->cache!==false && isset($this->cache[$key])) {
            return $this->cache[$key]; // use cached version
        }
        $stmt = $this->pdo->prepare($sql);
        if ($this->cache!==false) $this->cache[$key] = $stmt;
        return $stmt;
    }

    /**
     * Disables any query caching in place.
     *
     * @return T_Pdo_Slave  fluent interface
     */
    function disableCache()
    {
        $this->cache = false;
        return $this;
    }

}
