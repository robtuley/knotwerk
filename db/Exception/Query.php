<?php
/**
 * Contains the class T_Exception_Query.
 *
 * @package db
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Db Query or Connection Exception.
 *
 * @package db
 */
class T_Exception_Query extends T_Exception_Db
{

    /**
     * Attempts a transaction rollback where possible.
     *
     * @param T_Db_Slave $conn  database connection
     * @param string $message  error message
     * @param int $code  error code
     */
    function __construct($conn,$message='',$code=0)
    {
        parent::__construct($message,$code);
        if (($conn instanceof T_Db_Master) && !$conn->isCommitted()) {
            $conn->rollback();
        }
    }

}
