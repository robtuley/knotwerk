<?php
/**
 * Contains T_Code_Files class.
 *
 * @package client
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A group of source files.
 *
 * @package client
 */
class T_Code_Files
{

    /**
     * Directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * Names.
     *
     * @var string[]
     */
    protected $names;

    /**
     * Extension (includes the dot).
     *
     * @var string
     */
    protected $ext;

    /**
     * Whether group is complete codebase.
     *
     * @var bool
     */
    protected $complete = false;

    /**
     * Create source group.
     *
     * @param string $dir  directory
     * @param string[] $names  filenames
     * @param string $ext  extension (includes dot e.g. '.css')
     */
    function __construct($dir,array $names,$ext)
    {
        $this->dir = rtrim($dir,DIRECTORY_SEPARATOR.'/').DIRECTORY_SEPARATOR;
        $this->names = $names;
        $this->ext = $ext;

        // check files exist
        foreach ($this->getPaths() as $p)
        {
            if (!file_exists($p)) throw new RuntimeException("$p does not exist");
        }
    }

    /**
     * Gets a version number based on the file mod times.
     *
     * @return string
     */
    function getVersion()
    {
        $paths = $this->getPaths();
        sort($paths); // always put in predictable order so doesn't depend on order
        $hash = '';
        foreach ($paths as $p) {
            $hash .= $p.filemtime($p);
        }
        return md5($hash);
    }

    /**
     * Gets an array of filepaths.
     *
     * @return string
     */
    function getPaths()
    {
        $paths = array();
        foreach ($this->names as $name) {
            $paths[] = $this->dir.$name.$this->ext;
        }
        return $paths;
    }

    /**
     * Whether the group represents the complete codebase.
     *
     * @return bool
     */
    function isComplete()
    {
        return $this->complete;
    }

    /**
     * Flag the group as a complete codebase.
     *
     * @return T_Code_Group  fluent interface
     */
    function setComplete()
    {
        $this->complete = true;
        return $this;
    }

}