<?php
/**
 * Contains the T_Db_Result interface.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a DB result object.
 *
 * @package db
 */
interface T_Db_Result extends Iterator,Countable
{

    /**
     * Returns an associative array of row, or false on completion.
     *
     * @return mixed[]  associative array
     */
    function fetch();

    /**
     * Gets all rows as an array.
     *
     * @return array   array of rows
     */
    function fetchAll();

}
