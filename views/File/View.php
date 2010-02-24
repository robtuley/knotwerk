<?php
/**
 * Contains the class T_File_View.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * View of a Static File.
 *
 * This class encapsulates a static file to view.
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_File_View extends T_File_Path implements T_View
{

    /**
     * Create view.
     *
     * @param string $dir  directory
     * @param string $file  filename
     * @param string $ext  file extension
     */
    function __construct($dir,$file,$ext)
    {
        parent::__construct($dir,$file,$ext);
        if (!$this->exists()) {
            throw new T_Exception_File(parent::__toString(),'does not exist');
        }
    }

    /**
     * Outputs the file to the output buffer.
     *
     * @return T_View  fluent interface
     */
    function toBuffer()
    {
        $ok = @readfile(parent::__toString());
        if ($ok===false) {
            throw new T_Exception_File(parent::__toString(),'could not be rendered to buffer');
        }
        return $this;
    }

    /**
     * Outputs the entire file contents on render.
     *
     * @return string  file contents
     */
    function __toString()
    {
        return $this->getContent();
    }

}