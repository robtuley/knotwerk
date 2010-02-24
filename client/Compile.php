<?php
/**
 * Contains T_Compile class.
 *
 * @package client
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An interface for code compilers.
 *
 * @package client
 */
interface T_Compile
{

    /**
     * Registers the filter to compile an array of files.
     *
     * @param T_Code_Group  group of files to compile
     * @return T_Compile  fluent interface
     */
    function compile(T_Code_Files $files);
    
    /**
     * Gets the compiled source code (blocks to wait for compilation to finish).
     *
     * @return string
     */
    function getSrc();

}