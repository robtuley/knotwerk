<?php
/**
 * Contains the Package_Gateway class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Package gateway.
 *
 * @package reflection
 */
class Package_Gateway
{

    /**
     * Cached data.
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Get all packages.
     *
     * @return Package[]
     */
    function getAll()
    {
        $dir = new T_File_Dir(T_ROOT_DIR);
        foreach ($dir as $sub) {
            if (!($sub instanceof T_File_Dir)) continue;
            $ds = DIRECTORY_SEPARATOR;
            $name = _end(explode($ds,rtrim($sub->__toString(),$ds)));
            if (ctype_alpha(substr($name,0,1))) {
                if (!isset($this->cache[$name])) {
                    $this->cache[$name] = new Package($name);
                }
            }
        }
        ksort($this->cache);
        return $this->cache;
    }

    /**
     * Get package by name.
     *
     * @param string $name
     * @return Package
     */
    function getByName($name)
    {
        if (!isset($this->cache[$name])) {
            if (ctype_alnum($name) && is_dir(T_ROOT_DIR.$name)) {
                $this->cache[$name] = new Package($name);
            } else {
                throw new InvalidArgumentException("$name is not a package");
            }
        }
        return $this->cache[$name];
    }

}
