<?php
/**
 * Contains the class T_File_Swap.
 *
 * This file contains the T_File_Swap file class (read and write modes only,
 * atomic write commit.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Atomic Write File.
 *
 * This class is used to read and write from a file that uses file 'swapping'
 * instead of locking to achieve safe multi-thread execution. If the file is
 * opened in 'read' mode the file is treated as normal. If opened in write mode,
 * a temporary scratch file is opened and used for all write operations. To
 * commit these changes, the method commitWrite() is called to close the file
 * and atomically rename it to the intended filename. Note that this process is
 * only atomic if the filename is on a LOCAL rather than NETWORK disc!
 *
 * The usage of the class is the same as T_File_Unlocked, the 'swap' process is
 * entirely transparent.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_File_Swap extends T_File_Unlocked
{

    /**
     * Intended final filename (used for writes)
     *
     * @var T_File_Path
     */
    protected $fswap = false;

    /**
     * Open file.
     *
     * The constructor calls the fopen function to open the file handle and
     * throws a T_Exception_File on failure. The input $mode is also checked
     * for portability (it should include the 'b' binary indicator so original
     * line endings are preserved). The input modes available are:
     *
     *  'rb'   reading only.
     *  'wb'   writing only, truncate or create file.
     *
     * @param T_File_Path $fname  full path to file
     * @param string $mode  open mode (including 'b' indicator)
     */
    function __construct(T_File_Path $fname,$mode)
    {
        if (strcmp('wb',$mode)==0) {
            $this->fswap = $fname;
            $fname = new T_File_Path($fname->getDirName(),
                                     uniqid(rand(),true),null);
                // ^ make a tmp file in same dir as target
        }
        parent::__construct($fname,$mode);
    }

    /**
     * Checks valid file mode.
     *
     * Overwrites the parent file mode check (called in parent construct) to
     * allow only 'rb' or 'wb' modes.
     *
     * @param string $mode  file open mode to check
     */
    protected function assertMode($mode)
    {
        if (!(strcmp('wb',$mode)==0) && !(strcmp('rb',$mode)==0)) {
            $msg = "Only 'rb' or 'wb' modes permitted.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * "Swap" a write file on close.
     *
     * If the file has been opened in write mode, the file being written has
     * been opened and executed in a temporary location. When the file is
     * closed, the write needs to be committed (atomically) by renaming the
     * temporary file to its proper filename.
     */
    function close()
    {
        parent::close(); // prevents code recursively calling this fn.
        if ($this->fswap !== false) {
            $this->rename($this->fswap);
            $this->fswap = false;
        }
    }

}
