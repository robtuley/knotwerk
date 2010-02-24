<?php
/**
 * Contains the class T_Sqlite_Factory.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Sqlite connection factory.
 *
 * @package db
 */
class T_Sqlite_Connection extends T_Pdo_Connection
{

    /**
     * Create Sqlite connection.
     *
     * @param string $file  filename
     * @param int $mode   mode
     */
    function __construct($file,$mode=0666)
    {
        parent::__construct("sqlite:$file");
    }

    /**
     * Gets the DB type name.
     *
     * @param function $filter  optional filter
     * @return string
     */
    function getName($filter=null)
    {
        return _transform('SQLite',$filter);
    }

}
