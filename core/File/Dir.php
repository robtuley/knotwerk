<?php
/**
 * Defines the class T_File_Dir.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Filesystem Directory.
 *
 * This class encapsulates an existing filesystem directory. It can also be used
 * to traverse the files within a directory. For example:
 *
 * <code>
 * $dir = new T_File_Dir('some/path/to/data/');
 * foreach ($dir as $file) {
 *     if ($file->isFile()) {
 *       // ... operate on each file ...
 *     }
 * }
 * </code>
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_File_Dir implements Iterator
{

    /**
     * Default directory creation permissions
     *
     * @global
     */
    const DEFAULT_MODE = 0777;

    /**
     * Directory (includes trailing slash).
     *
     * @var string
     */
    protected $dir;

    /**
     * Iterator next component validity.
     *
     * @var bool
     */
    private $valid = false;

    /**
     * Iterator key value.
     *
     * @var int
     */
    private $key = 0;

    /**
     * Current iterator path object.
     *
     * @var T_File_Dir|T_File_Path
     */
    private $current = false;

    /**
     * Directory handle.
     *
     * @var resource
     */
    protected $dh = false;

    /**
     * Specify directory string.
     *
     * @param string $dir  directory string
     */
    function __construct($dir,$mode=self::DEFAULT_MODE)
    {
        if (strlen($dir)>0) {
            $dir = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$dir);
               // ^ normalise dir separators, required for recursive dir
               //   creation to work on windows
            $this->dir = rtrim($dir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        } else {
            $this->dir = '.'.DIRECTORY_SEPARATOR;
        }
        /* ensure is a directory */
        if (!is_dir($this->dir)) {
            if (@mkdir($this->__toString(),$mode,true) === false) {
                    // supports recursive creation ^
                $msg = 'could not create dir';
                throw new T_Exception_File($this->__toString(),$msg);
            }
        }
    }

    /**
     * Gets the string path.
     *
     * @return string  encapsulated path
     */
    function __toString()
    {
        return $this->dir;
    }

    /**
     * Open directory handle.
     */
    protected function openDirHandle()
    {
        if ($this->dh === false) {
            $this->dh = @opendir($this->dir);
        }
        if ($this->dh === false) {
            throw new T_Exception_File($this->dir,'dir handle open failure');
        }
    }

    /**
     * Gets a sub directory.
     *
     * @param string $path  sub directory name
     * @return T_File_Dir  path to a sub-object with this directory.
     */
    function getChildDir($path)
    {
        return new T_File_Dir($this->__toString().$path);
    }

    /**
     * Rewind directory iterator.
     */
    function rewind()
    {
        $this->openDirHandle();
        rewinddir($this->dh);
        $this->next();  // prepare first value
        $this->key = 0;
    }

    /**
     * Return the current filepath.
     *
     * @return T_File_Dir|T_File_Path  the current file or dir element
     */
    function current()
    {
        return $this->current;
    }

    /**
     * Return the key of the current file/dir element.
     *
     * @return int  current element key
     */
    function key()
    {
        return $this->key;
    }

    /**
     * Move cursor to next file/dir element.
     */
    function next()
    {
        $this->current = readdir($this->dh);
        $this->valid = (false !== $this->current);
        if ($this->valid) {
            /* check is not a dot or double dot */
            if (strcmp($this->current,'.')===0 || strcmp($this->current,'..')===0) {
                $this->next();
                return;
            }
            /* convert to appropriate object */
            $path = $this->dir.$this->current;
            if (is_file($path)) {
                /* parse filename extension */
                $ext = strrchr($this->current,'.');
                $file = substr($this->current,0,strlen($this->current)-strlen($ext));
                $ext = substr($ext,1);
                $this->current = new T_File_Path($this->dir,$file,$ext);
            } elseif (is_dir($path)) {
                $this->current = new T_File_Dir($this->dir.$this->current);
            } else {
                throw new T_Exception_File($path,'Unknown file system type');
            }
        }
        $this->key++;
    }

    /**
     * Validity of current element.
     *
     * @return bool  validity of the next element
     */
    function valid()
    {
        return $this->valid;
    }

    /**
     * Delete directory.
     */
    function delete()
    {
        /* delete contents */
        foreach ($this as $content) {
            $content->delete();
        }
        /* remove directory */
        $this->__destruct();
        if (@rmdir($this->__toString()) === false) {
            throw new T_Exception_File($this->__toString(),'could not delete');
        }
        clearstatcache();
    }

    /**
     * Close directory handle on destruct.
     */
    function __destruct()
    {
        if ($this->dh !== false) {
            closedir($this->dh);
            $this->dh = false;
        }
    }

}
