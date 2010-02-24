<?php
/**
 * Contains the T_Code_DocBlockTypeTag class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A code DocBlock tag that describes a variable with type (e.g. 'return', 'var').
 *
 * @package reflection
 */
class T_Code_DocBlockTypeTag extends T_Code_DocBlockTag
{

    /**
     * Type (e.g. 'string', 'bool')
     *
     * @var string
     */
    protected $type;

    /**
     * Whether type is an array of these types.
     *
     * @var bool
     */
    protected $is_array;

    /**
     * Create tag.
     *
     * @param string $name
     * @param string $type e.g. string, string[] is an array of strings
     * @param string $desc
     */
    function __construct($name,$type,$desc)
    {
        parent::__construct($name,$desc);
        $this->is_array = (substr($type,strlen($type)-2)==='[]');
        if ($this->is_array) {
            $this->type = substr($type,0,strlen($type)-2);
        } else {
            if ($type==='array') {
                $this->is_array = true;
                $this->type = null;
            } else {
                $this->type = $type;
            }
        }
    }

    /**
     * Gets the type.
     *
     * @param function $filter
     * @return string
     */
    function getType($filter=null)
    {
        return _transform($this->type,$filter);
    }

    /**
     * Whether is an array of these types.
     *
     * @return bool
     */
    function isArray()
    {
        return $this->is_array;
    }

    /**
     * Return string representation of type.
     *
     * @param function $filter
     * @return string
     */
    function getCombinedType($filter=null)
    {
        $type = ($this->type) ? $this->type : 'unknown';
        if ($this->isArray()) $type .= '[]';
        return _transform($type,$filter);
    }

}
