<?php
/**
 * Contains the class T_File_PathUrl.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Path to a file in web directory that has an associated URL.
 *
 * @package core
 */
class T_File_PathUrl extends T_File_Path
{

    /**
     * The relative URL path from web root.
     *
     * @var array
     */
    protected $url_path;

    /**
     * Specify path.
     *
     * @param string $web_root dir for web root
     * @param string $rel_dir  rel dir from web root
     * @param string $file  filename
     * @param string $ext  extension
     */
    function __construct($web_root,$rel_dir,$file,$ext)
    {
        // split relative path into path sub-sections
        $rel_dir = trim(trim($rel_dir,'/'),DIRECTORY_SEPARATOR);
        if (strcmp('/',DIRECTORY_SEPARATOR)!==0) {
            $this->url_path = preg_split('#['.preg_quote('/'.DIRECTORY_SEPARATOR).']#',$rel_dir);
        } else {
            $this->url_path = explode('/',$rel_dir);
        }
        // concatenate dirs and exe parent
        $dir = rtrim(rtrim($web_root,'/'),DIRECTORY_SEPARATOR).
               DIRECTORY_SEPARATOR.$rel_dir;
        parent::__construct($dir,$file,$ext);
    }

    /**
     * Create a URL from the root path provided.
     *
     * @param T_Url $root
     */
    function makeUrl(T_Url $root)
    {
        $url = clone($root);
        foreach ($this->url_path as $path) $url->appendPath($path);
        $filename =  $this->getFilename();
        if (strlen($ext=$this->getMime()->getExt())>0) {
            $filename .= '.'.$ext;
        }
        $url->appendPath($filename);
        return $url;
    }

}