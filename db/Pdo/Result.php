<?php
/**
 * Contains the class T_Pdo_Result.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * PDO Result.
 *
 * @package db
 */
class T_Pdo_Result implements T_Db_Result
{

    /**
     * PDO Statement.
     *
     * @var PDOStatement
     */
    protected $stmt;

    /**
     * Current key value.
     *
     * @var int
     */
    protected $key = 0;

    /**
     * Cache of results.
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Whether the cache is complete.
     *
     * @var unknown_type
     */
    protected $is_cached = false;

    /**
     * Whether current row is valid.
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * Create result.
     *
     * @param PDOStatement $result
     */
    function __construct($stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Returns an associative array of row, or false on completion.
     *
     * @return mixed[]  associative array
     */
    function fetch()
    {
        if ($this->is_cached) {
            // when the result is cached, we can't just call next as this will
            // skip the first row.
            $row = $this->valid() ? $this->current() : false;
            $this->next();
            return $row;
        } else {
            return $this->next();
        }
    }

    /**
     * Gets all rows as an array.
     *
     * @return array   array of rows
     */
    function fetchAll()
    {
        if ($this->is_cached) {
            $this->rewind();
            return $this->cache;
        } else {
            $this->is_cached = true;
            try {
                $this->cache = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new T_Exception_Query($this,$e->getMessage(),$e->getCode());
            }
            $this->rewind();
            return $this->cache;
        }
    }

    // Iterator:
    // $result->rewind();
    // while ($result->valid()) {
    //   $value = $result->current();
    //   $result->next();
    // }

    /**
     * Rewind iterator, sets up first row.
     */
    function rewind()
    {
        if ($this->is_cached) {
            return ($this->valid = (false !== reset($this->cache)));
        } else {
            return $this->next();
        }
    }

    /**
     * Returns current row.
     *
     * @return array  current row
     */
    function current()
    {
        return current($this->cache);
    }

    /**
     * Returns current key value.
     *
     * @return int iteration key
     */
    function key()
    {
        return key($this->cache);
    }

    /**
     * Increments iterator to next entry.
     */
    function next()
    {
        if ($this->is_cached) {
            $row = next($this->cache);
            $this->valid = (false !== $row);
            return $row;
        } else {
            try {
                $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
                if ($row!==false) {
                    $this->valid = true;
                    $this->cache[] = $row;
                    end($this->cache);
                    return $row;
                } else {
                    $this->is_cached = true; // cache is complete
                    return ($this->valid=false);
                }
            } catch (PDOException $e) {
                throw new T_Exception_Query($this,$e->getMessage(),$e->getCode());
            }
        }
    }

    /**
     * Whether the current value is valid.
     *
     * @return bool  whether current() is valid
     */
    function valid()
    {
        return $this->valid;
    }

    /**
     * Counts the number of rows in a result set.
     *
     * @return int
     */
    function count()
    {
        // PDOStatement::rowCount() is unreliable, so have to use the cached
        // array instead..
        if (!$this->is_cached) $this->fetchAll();
        return count($this->cache);
    }

}
