<?php
/**
 * Defines the class T_Unit_PackageSuite.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test suite for an entire package.
 *
 * @package unit
 */
class T_Unit_PackageSuite extends T_Unit_Directory
{

    /**
     * Package.
     *
     * @var string
     */
    protected $package;

    /**
     * Build test suite.
     *
     * @param string $package  package
     * @param string $deps  array of other package dependencies
     */
    function __construct($package)
    {
        $this->package = $package;
        parent::__construct(T_ROOT_DIR.$package.'/Test','T_Test');
    }

}
