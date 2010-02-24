<?php
/**
 * Contains the T_Db_Master interface.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a DB master object.
 *
 * @package db
 */
interface T_Db_Master extends T_Db_Slave
{

    /**
     * Load multiple SQL write queries (e.g. build a db).
     *
     * Note that in most drivers the main SQL file passed in is simply split
     * into separate queries. The file is split by separating the file on a
     * semi-colon FOLLOWED BY A LINE RETURN. This allows for loading of for
     * example SQLite triggers as a single query that include two semi-colons
     * as long as you make sure only the final semi-colon is followed by a
     * new line.
     *
     * @param string $sql  sql query
     * @return T_Db_Master  fluent interface
     */
    function load($sql);

    /**
     * Gets the last insert ID.
     *
     * @return int  last insert ID
     */
    function getLastId();

    /**
     * Begins a transaction.
     *
     * @return T_Db_Master  fluent interface
     */
    function begin();

    /**
     * Commits a transaction.
     *
     * @return T_Db_Master  fluent interface
     */
    function commit();

    /**
     * Rolls back a transaction.
     *
     * @return T_Db_Master  fluent interface
     */
    function rollback();

    /**
     * Whether we are currently in a transaction.
     *
     * @return bool
     */
    function isCommitted();

}
