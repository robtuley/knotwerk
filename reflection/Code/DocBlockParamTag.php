<?php
/**
 * Contains the T_Code_DocBlockParamTag class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A code DocBlock tag that describes a parameter with type and variable name.
 *
 * @package reflection
 */
class T_Code_DocBlockParamTag extends T_Code_DocBlockTypeTag
{

    /**
     * variable name
     *
     * @var string
     */
    protected $var;

    /**
     * Create tag.
     *
     * @param string $name
     * @param string $type e.g. string, string[] is an array of strings
     * @param string $var  variable name
     * @param string $desc
     */
    function __construct($name,$type,$var,$desc)
    {
        parent::__construct($name,$type,$desc);
        $this->var = $var;
    }

    /**
     * Gets the variable name.
     *
     * @param function $filter
     * @return string
     */
    function getVar($filter=null)
    {
        return _transform($this->var,$filter);
    }

}