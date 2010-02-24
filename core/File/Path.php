<?php
/**
 * Contains the class T_File_Path.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Path to a file.
 *
 * @package core
 */
class T_File_Path
{

    /**
     * File statistics.
     *
     * @var array
     */
    protected $stat = false;

    /**
     * Directory (includes trailing slash).
     *
     * @var string
     */
    protected $dir;

    /**
     * Filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * File extension.
     *
     * @var T_File_Mime
     */
    protected $mime;

    /**
     * Specify path required.
     *
     * @param string $dir  directory string
     * @param string $file  filename
     * @param string $ext  extension
     */
    function __construct($dir,$file,$ext)
    {
        if (strlen($dir)>0) {
            $this->dir = rtrim($dir,'/\\').DIRECTORY_SEPARATOR;
        } else {
            $this->dir = '.'.DIRECTORY_SEPARATOR;
        }
        $this->filename = (string) $file;
        $this->mime = new T_File_Mime($ext);
    }

    /**
     * Get directory string.
     *
     * @return string  directory name
     */
    function getDirName()
    {
        return $this->dir;
    }

    /**
     * Gets the filename string.
     *
     * @return string  filename
     */
    function getFilename()
    {
        return $this->filename;
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
     * Returns the full pathname.
     *
     * This function is delibertately different to the __toString() method so
     * that __toString() can be extended for different purposes
     * e.g. T_File_View.
     *
     * @return string
     */
    private function getPath()
    {
        $path =  $this->getDirName().$this->getFilename();
        if (strlen($this->getMime()->getExt())>0) {
            $path .= '.'.$this->getMime()->getExt();
        }
        return $path;
    }

    /**
     * Gets the string path.
     *
     * @return string  encapsulated path
     */
    function __toString()
    {
        return $this->getPath();
    }

    /**
     * Whether the files exists.
     *
     * @return bool whether the file exists
     */
    function exists()
    {
        return is_file($this->getPath());
    }

    /**
     * Deletes the file.
     *
     * @return null
     */
    function delete()
    {
        if (@unlink($this->getPath()) === false) {
            throw new T_Exception_File($this->getPath(),'could not delete');
        }
        clearstatcache();
        return $this;
    }

    /**
     * Loads the file stats if not available.
     *
     * @return null
     */
    protected function loadStats()
    {
        if ($this->stat === false) {
            $this->stat = @stat($this->getPath());
            if ($this->stat===false) {
                throw new T_Exception_File($this->getPath(),'stat error');
            }
        }
    }

    /**
     * Get the filesize in bytes.
     *
     * @return int  size of the file in bytes.
     */
    function getSize()
    {
        $this->loadStats();
        return $this->stat['size'];
    }

    /**
     * Human readable filesize.
     *
     * The input precision argument specifies the precision of the returned
     * result. For example:
     * <code>
     * $file = new T_File_Path(null,'test','txt');
     * $file->humanSize(0);  // might return 32MB,2MB,756KB,3KB,156B
     * $file->humanSize(2);  // might return 32.12MB,2.64MB, ... 156B
     * $file->humanSize(-1); // might return 30MB,2MB,760KB,3KB,160B
     * </code>
     *
     * @param int $precision  precision with which to return the size number.
     * @return string  human readable filesize
     */
    function getHumanSize($precision=1)
    {
        $bytes = $this->getSize();
        if ($bytes>1048576) {
            return round($bytes/1048576,$precision).'MB';
        } elseif ($bytes>1024) {
            return round($bytes/1024,$precision).'KB';
        } else {
            return round($bytes,$precision).'B';
        }
    }

    /**
     * Get the last modified time of file.
     *
     * @return int  file last modification time
     */
    function getLastModified()
    {
        $this->loadStats();
        return $this->stat['mtime'];
    }

    /**
     * Rename the current file to a new filename.
     *
     * @param T_File_Path $path  new filepath for file
     */
    function rename(T_File_Path $path)
    {
        if (T_WINDOWS && $path->exists()) {
            $path->delete();
              /* required for windows, but weakens application as possibility
                 of failure to rename resulting in just file deletion  */
        }
        $ok = @rename($this->getPath(),$path->__toString());
        if (!$ok) {
            $msg = 'rename to '.$path->__toString().' failed';
            throw new T_Exception_File($this->getPath(),$msg);
        }
        $this->dir = $path->getDirName();
        $this->filename = $path->getFilename();
        $this->mime = $path->getMime();
        return $this;
    }

    /**
     * Copy file to another location.
     *
     * @param T_File_Path $path  filepath for file to copy to
     */
    function copyTo(T_File_Path $path)
    {
        $ok = @copy($this->getPath(),$path->__toString());
        if (!$ok) {
            $msg = 'copy to '.$path->__toString().' failed';
            throw new T_Exception_File($this->getPath(),$msg);
        }
        return $this;
    }

	/**
     * Get file content.
     *
     * This gets the file content as a string, or null if the file does not exist.
     *
     * @param function $filter  optional filter
     * @return string  file contents
     */
    function getContent($filter=null)
    {
        if (!$this->exists()) {
            $data = null;
        } else {
            $data = @file_get_contents($this->getPath());
            if ($data === false) {
                throw new T_Exception_File($this->getPath(),"Could not get content");
            }
        }
        return _transform($data,$filter);
    }

    /**
     * Clone additions.
     */
    function __clone()
    {
        /* clone MIME object */
        $this->mime = new T_File_Mime($this->mime->getExt());
    }

}
