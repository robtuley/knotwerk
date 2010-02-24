<?php
/**
 * Contains the class T_File_Uploaded.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates an uploaded file.
 *
 * @package core
 */
class T_File_Uploaded
{

    /**
     * Size of the uploaded file (in bytes).
     *
     * @var int
     */
    protected $size;

    /**
     * MIME type of the file.
     *
     * @var T_File_Mime
     */
    protected $mime;

    /**
     * Original filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * Temporary filename.
     *
     * @var string
     */
    protected $path;

    /**
     * Encapsulate uploaded file.
     *
     * @param string $path  temporary file path
     * @param int $size  size of file in bytes
     * @param string $original  original filename
     */
    function __construct($path,$size,$original)
    {
        /* parse original filename */
        $ext = strrchr($original,'.');
        $file = substr($original,0,strlen($original)-strlen($ext));
        $ext = substr($ext,1);
        /* initialise properties */
        $this->mime = new T_File_Mime($ext);
        $this->size = $size;
        $this->filename = strlen($file)>0 ? $file : null;
        $this->path = $path;
    }

    /**
     * Gets MIME type.
     *
     * @return T_File_Mime  MIME type
     */
    function getMime()
    {
        return $this->mime;
    }

    /**
     * Gets the original filename.
     *
     * Note that this is a user provided value and should be checked! As such
     * the data is returned encapsulated in a cage object.
     *
     * @return T_Cage_Scalar  caged filename
     */
    function getFilename()
    {
        return new T_Cage_Scalar($this->filename);
    }

    /**
     * Gets the string path.
     *
     * @return string  encapsulated path
     */
    function __toString()
    {
        return $this->path;
    }

    /**
     * Get the filesize in bytes.
     *
     * @return int  size of the file in bytes.
     */
    function getSize()
    {
        return $this->size;
    }

    /**
     * Whether the uploaded file exists.
     *
     * @return bool
     */
    function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Rename the current file to a new filename.
     *
     * @param T_File_Path $path  new filepath for file
     */
    function rename(T_File_Path $path)
    {
        move_uploaded_file($this->path,$path->__toString());
    }

}