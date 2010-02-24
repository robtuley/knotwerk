<?php
/**
 * Contains the class T_File_Unlocked.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * No-locking File.
 *
 * This is the OKT base file class that wraps the common file handling
 * functions. The base class does not extend the SplFileObject class for several
 * reasons: support for this object in PHP 5.1.0 is a little sketchy, and the
 * SPL class does not have an explicit file close method, making file swap
 * extension impossible.
 *
 * This class does not incorporate any file locking or other methodology to
 * prevent corrupted reads, etc. from a multi-thread PHP server, and is not
 * thread-safe. It should be used with caution.
 *
 * The file is opened in the constructor, and common file manipulation functions
 * are wrapped (and error checked) by the class methods:
 *
 * <code>
 * $file = new T_File_Unlocked('somefile.txt','wb');
 * $file->write('some content');
 * $file->close();
 * </code>
 *
 * The file handle is closed on object destruction if not explicitally done so
 * earlier using the close() method.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_File_Unlocked implements Iterator
{
    /**
     * File handle.
     *
     * @var resource
     */
    protected $fp = false;

    /**
     * Filename.
     *
     * @var string
     */
    protected $fname;

    /**
     * Iterator position (line number).
     *
     * @var int
     */
    protected $key = 0;

    /**
     * Current data.
     *
     * @var string|false  current data
     */
    protected $current = false;

    /**
     * Open file.
     *
     * The constructor calls the fopen function to open the file handle and
     * throws a T_Exception_File on failure. The input $mode is also checked
     * for portability (it should include the 'b' binary indicator so original
     * line endings are preserved). The input modes available are:
     *
     *  'rb'   reading only.
     *  'rb+'  reading and writing.
     *  'wb'   writing only, truncate or create file.
     *  'wb+'  reading and writing, truncate or create file.
     *  'ab'   writing only, append or create file.
     *  'ab+'  reading and writing, append or create file.
     *  'xb'   writing only, must create file.
     *  'xb+'  reading and writing, must create file.
     *
     * @param T_File_Path $fname  full path to file
     * @param string $mode  open mode (including 'b' indicator)
     */
    function __construct($fname,$mode)
    {
        $this->assertMode($mode);
        $this->fname = $fname;
        $this->fp = @fopen($this->fname->__toString(),$mode,false);
        if ($this->fp === false) {
            throw new T_Exception_File($this->fname->__toString(),
                                        'open failure.');
        }
    }

    /**
     * Checks valid file mode.
     *
     * This function contains any checks on the passed in file mode extra to
     * those performed by fopen. In this case, we simply check that a 'b' has
     * been included, required for fully portable code.
     *
     * @param string $mode  file open mode to check
     */
    protected function assertMode($mode)
    {
        if (mb_strpos($mode,'b',1)===false) {
            $msg = "Portable code should include a 'b' in the mode.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Write content to file.
     *
     * Wraps the PHP fwrite function and writes a string directly to the file.
     * An T_Exception_File is thrown on failure.
     *
     * @param string $content  content to write to the file
     * @param int $len  length of the string to write to the file (bytes)
     * @return int  number of bytes that have been written
     */
    function write($content,$len=null)
    {
        if (strlen($content)==0) {
            return 0; // no content to write
        }
        if ($len === null) {
            $bytes = @fwrite($this->fp,$content);
        } else {
            $bytes = @fwrite($this->fp,$content,$len);
        }
        if (!$bytes) {  // returns >0 on success
            throw new T_Exception_File($this->fname->__toString(),'file write failure');
        }
        return $bytes;
    }

    /**
     * Read content from file.
     *
     * Wraps the PHP fread function and reads a string of length $len directly
     * from the file. An T_Exception_File is thrown on failure.
     *
     * @param int $len  number of bytes to read
     * @return string  data read
     */
    function read($len)
    {
        $data = @fread($this->fp,$len);
        if ($data === false) {
            throw new T_Exception_File($this->fname->__toString());
        }
        return $data;
    }

    /**
     * Send contents to buffer.
     *
     * Wraps the PHP function fpassthru which simply dumps the content of the
     * file from the current file pointer to the end of the file to the output
     * buffer.
     */
    function sendToBuffer()
    {
        $ok = @fpassthru($this->fp);
        if ($ok === false) {
            throw new T_Exception_File($this->fname->__toString());
        }
        return $this;
    }

    /**
     * Close the file.
     *
     * Closes the file handle, or throws an T_Exception_File if this is not
     * possible.
     */
    function close()
    {
        $ok = fclose($this->fp);
        if (!$ok) {
            throw new T_Exception_File($this->fname->__toString());
        }
        $this->fp = false;
        return $this;
    }

    /**
     * Check whether file is open.
     *
     * @return bool  whether the file handle is still open
     */
    function isOpen()
    {
        return is_resource($this->fp);
    }

    /**
     * Rename file.
     *
     * This method renames the current file. If the file is open, it will close
     * the file first.
     *
     * @param T_File_Path $filename  new filename
     * @return null
     */
    function rename(T_File_Path $filename)
    {
        if ($this->isOpen()) {
            $this->close();
        }
        $this->fname->rename($filename);
        $this->fname = $filename;
        return $this;
    }

    /**
     * Deconstructor closes file if necessary.
     */
    function __destruct()
    {
        if ($this->isOpen()) {
            $this->close();
        }
    }

    /**
     * This methods move the file pointer to the next row.
     *
     * @return void
     */
    function next()
    {
        $this->loadLine();
        $this->key += 1;
    }

    /**
     * Reset the file handler.
     *
     * @return void
     */
    function rewind()
    {
        rewind($this->fp);
        $this->loadLine();
        $this->key = 0;
    }

    /**
     * Gets the current line number.
     *
     * @return int
     */
    function key()
    {
        return $this->key;
    }

    /**
     * Gets the current line data.
     *
     * @return string  file line
     */
    function current()
    {
        return $this->current;
    }

    /**
     * Whether the current line is readable.
     *
     * @return boolean  whether current line is readable.
     */
    function valid()
    {
        return $this->current !== false;
    }

    /**
     * Loads the next line of the file.
     *
     * @return void
     */
    protected function loadLine()
    {
        if (feof($this->fp)) {
            $this->current = false;
        } else {
            $this->current = trim(fgets($this->fp));
        }
    }

}