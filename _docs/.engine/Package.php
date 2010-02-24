<?php
/**
 * Defines the
 * Package class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Library package.
 *
 * @package reflection
 */
class Package
{

    /**
     * Package name.
     *
     * @var string
     */
    protected $alias;

    /**
     * Cached data.
     *
     * @var array
     */
    protected $cached = array();

    /**
     * Create package.
     *
     * @param string $alias  e.g. core, mysql
     */
    function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Gets the alias.
     *
     * @param function $filter
     * @return string  package alias.
     */
    function getAlias($filter=null)
    {
        return _transform($this->alias,$filter);
    }

    /**
     * Gets the description.
     *
     * @param function $filter
     * @return string  text description of package.
     */
    function getDesc($filter=null)
    {
        if (!isset($this->cached['desc'])) {
            $this->cached['desc'] = null;
            $readme = new T_File_Path($this->getDir(),'readme','txt');
            if ($readme->exists()) {
                $this->cached['desc'] = $readme->getContent();
            }
        }
        return _transform($this->cached['desc'],$filter);
    }

    /**
     * Gets the name.
     *
     * @param function $filter
     * @return string  package name.
     */
    function getName($filter=null)
    {
        $name = ucfirst($this->alias);
        if (strlen($name)<=3) {
            $name = strtoupper($name); // probably a acronym
        }
        return _transform($name,$filter);
    }

    /**
     * Gets a list of classes in the package.
     *
     * This gets a list of classes in the package, exclduing any test classes.
     * The list is built from looking at the filesystem, and the list is cached
     * after the first call.
     *
     * @return string[]
     */
    function getClassnames()
    {
        if (!isset($this->cached['classes'])) {
            $this->cached['classes'] = $this->searchDirForClass(new T_File_Dir($this->getDir()));
        }
        return $this->cached['classes'];
    }

    /**
     * Gets package directory name.
     *
     * @return string
     */
    function getDir()
    {
        return T_ROOT_DIR.$this->alias.DIRECTORY_SEPARATOR;
    }

    /**
     * Search a directory for class files.
     *
     * @param T_File_Dir $dir  directory of class files
     * @param string $prefix  prefix for classes
     * @return string[]  class names
     */
    protected function searchDirForClass(T_File_Dir $dir,$prefix='T')
    {
        $classes = array();
        foreach ($dir as $file) {
            if ($file instanceof T_File_Dir) {
                // recurse, making sure we skip any:
                //   (a) testing directories ('Test')
                //   (b) hidden directories (starting with '.')
                //   (c) reference directories (beginning with '_')
                $ds = DIRECTORY_SEPARATOR;
                $end = _end(explode($ds,rtrim($file->__toString(),$ds)));
                if (strncmp($end,'.',1)!==0 &&
                    strncmp($end,'_',1)!==0 &&
                    strcmp($end,'Test')!==0) {
                    $new_prefix = $end;
                    if (strlen($prefix)>0) {
                        $new_prefix = $prefix.'_'.$new_prefix;
                    }
                    $classes = array_merge($classes,$this->searchDirForClass($file,$new_prefix));
                }
            } else {
                if ($file->getMime()->getType()!==T_Mime::PHP) continue;
                if (strncmp($file->getFilename(),'.',1)===0) continue;
                if (strncmp($file->getFilename(),'_',1)===0) continue;

                $classname = $file->getFilename();
                if (strlen($prefix)>0) $classname = $prefix.'_'.$classname;
                $classes[] = $classname;
            }
        }
        return $classes;
    }

}
