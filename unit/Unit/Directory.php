<?php
/**
 * Defines the class T_Unit_Directory.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Unit test directory.
 *
 * This class encapsulates a directory of classes with a certain prefix. It
 * examines the files in the directory builds all available test cases into a
 * single test suite.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
class T_Unit_Directory extends T_Unit_Suite
{

    /**
     * Adds all class files that are test cases to suite.
     *
     * @param string $dir  directory to gather files from
     * @param string $prefix  class suffix
     */
    function __construct($dir,$prefix)
    {
        $prefix = $prefix.'_';
        $dir = new T_File_Dir($dir);
        $this->processDir($dir,$prefix);
    }

    /**
     * Process a directory.
     *
     * @param T_File_Dir $dir
     * @param string $prefix
     */
    function processDir($dir,$prefix)
    {
        foreach ($dir as $file) {
            if ($file instanceof T_File_Dir) {
                // recurse!
                $path = explode(DIRECTORY_SEPARATOR,rtrim($file->__toString(),DIRECTORY_SEPARATOR));
                $path = array_pop($path);
                if (strncmp($path,'.',1)!==0 && strncmp($path,'_',1)!==0 ) {
                    // ^ skip hidden directory (e.g. .svn) and underscore at start e.g. _sample
                    $this->processDir($file,$prefix.$path.'_');
                }
            } else {
                $class = $prefix.$file->getFilename();
                if (class_exists($class,true)) {
                    $reflect = new ReflectionClass($class);
                    if ($reflect->isSubclassOf('T_Unit_Test') && !$reflect->isAbstract()) {
                        $this->addChild(new $class());
                    }
                }
            }
        }
    }

}
