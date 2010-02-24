<?php
/**
 * Contains the class T_Exception_File.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * File Exception.
 *
 * A file exception is throw when a problem is encountered during file handling,
 * and can be classed as a runtime error.
 *
 * @package core
 * @see OKT_exceptionHandler
 * @license http://knotwerk.com/licence MIT
 */
class T_Exception_File extends RuntimeException
{

    /**
     * Filename with which exception occurred.
     *
     * @var string
     */
    protected $fname;

    /**
     * Construct exception.
     *
     * This exception requires an extra input argument, the filename with which
     * the exception occurred.
     *
     * @param string $filename
     * @param string $message
     * @param int $code
     */
    function __construct($filename,$message='',$code=0)
    {
        $message = '['.$filename.'] '.$message;
        parent::__construct($message,$code);
        $this->fname = $filename;
    }
}