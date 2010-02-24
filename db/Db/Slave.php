<?php
/**
 * Contains the T_Db_Slave interface.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a DB slave object.
 *
 * @package db
 */
interface T_Db_Slave extends T_Filter
{

    /**
     * Executes a select query.
     *
     * @param string $sql  SQL to execute
     * @param array  $bind  values to bind into the query
     * @return T_Db_Result
     */
    function query($sql,array $bind=array());

    /**
     * Executes a query and returns a single value.
     *
     * A typical use case for this function would be 'SELECT COUNT(*)'.. It
     * executes a query and retrieves a single scalar value.
     *
     * @param string $sql
     * @param array  $bind  values to bind into the query
     * @return mixed  scalar value that has been queried
     */
    function queryAndFetch($sql,array $bind=array());

    /**
     * Disables any query caching in place.
     *
     * Most drivers will cache prepared statements for re-use with new values,
     * and this function is called to disable any of this functionality. This
     * is required during install scripts of similar when the schema may be
     * changing (for example, SQLIte doesn't permit prepared query re-use
     * after any schema changes).
     *
     * @return T_Db_Slave  fluent interface
     */
    function disableCache();

}
