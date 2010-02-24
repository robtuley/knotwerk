<?php
/**
 * Contains the T_Code_DocBlockTag class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A code DocBlock tag.
 *
 * @package reflection
 */
class T_Code_DocBlockTag
{

    /**
     * Tag name (e.g. 'param').
     *
     * @var string
     */
    protected $name;

    /**
     * Tag desc.
     *
     * @var string
     */
    protected $desc;

    /**
     * Create DocBlock tag.
     *
     * @param string $name
     * @param string $desc
     */
    function __construct($name,$desc)
    {
        $this->name = $name;
        $this->desc = $desc;
    }

    /**
     * Whether it is a particular type.
     *
     * @param string $name
     * @return bool
     */
    function is($name)
    {
        return strcasecmp($this->name,$name)===0;
    }

    /**
     * Gets the tag name.
     *
     * @param function $filter
     * @return string
     */
    function getName($filter=null)
    {
        return _transform($this->name,$filter);
    }

    /**
     * Gets the tag description.
     *
     * @param function $filter
     * @return string
     */
    function getDesc($filter=null)
    {
        return _transform($this->desc,$filter);
    }

}