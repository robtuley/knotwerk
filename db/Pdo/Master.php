<?php
/**
 * Contains the class T_Pdo_Master.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * PDO Master Skeleton.
 *
 * @package db
 */
class T_Pdo_Master extends T_Pdo_Slave implements T_Db_Master
{

    /**
     * Whether we are in a transaction.
     *
     * @var bool
     */
    protected $is_committed = true;

    /**
     * Execute write queries.
     *
     * @param string $sql  sql query
     * @return T_Db_Master  fluent interface
     */
    function load($sql)
    {
        $sql = array_filter(preg_split('/;\r?\n/',$sql),'trim');
          // ^ split on ; followed by line return
        try {
            foreach ($sql as $stmt) {
                if (false===$this->pdo->exec($stmt)) {
                    throw new T_Exception_Query($this,"$stmt failed");
                }
            }
        } catch (PDOException $e) {
            throw new T_Exception_Query($this,"$stmt failed: ".$e->getMessage());
        }
        return $this;
    }

    /**
     * Write is permitted on master.
     *
     * @return bool
     */
    protected function isWritePermitted()
    {
        return true;
    }

    /**
     * Gets the last insert ID.
     *
     * @param string $seq  optional sequence name
     * @return int  last insert ID
     */
    function getLastId($seq=null)
    {
        $id = $this->pdo->lastInsertId($seq);
        return ($id ? intval($id) : null);
    }

    /**
     * Begins a transaction.
     *
     * @return T_Db_Master  fluent interface
     */
    function begin()
    {
        $this->is_committed = false;
        try {
            $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            throw new T_Exception_Db($e->getMessage());
        }
        return $this;
    }

    /**
     * Commits a transaction.
     *
     * @return T_Db_Master  fluent interface
     */
    function commit()
    {
        $this->is_committed = true;
        try {
            $this->pdo->commit();
        } catch (PDOException $e) {
            throw new T_Exception_Db($e->getMessage());
        }
        return $this;
    }

    /**
     * Rolls back a transaction.
     *
     * @return T_Db_Master  fluent interface
     */
    function rollback()
    {
        $this->is_committed = true;
        try {
            $this->pdo->rollBack();
        } catch (PDOException $e) {
            throw new T_Exception_Db($e->getMessage());
        }
        return $this;
    }

    /**
     * Whether we are currently in a transaction.
     *
     * @return bool
     */
    function isCommitted()
    {
        return $this->is_committed;
    }

}
