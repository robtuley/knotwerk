<?php
/**
 * Defines the T_Text_List class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A list container.
 *
 * @package wiki
 */
class T_Text_List extends T_Text_Composite
{

    /**
     * List types.
     */
    const ORDERED = '-';
    const UNORDERED = '*';

    /**
     * List type.
     *
     * @var string
     */
    protected $type;

    /**
     * Create list.
     *
     * @param string $type
     */
    function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Whether the list is of a particular type.
     *
     * @param int $type
     * @return bool
     */
    function is($type)
    {
        return strcmp($type,$this->type)===0;
    }

    /**
     * Returns original formatted full text.
     */
    function __toString()
    {
        $count = isset($this->parents[get_class($this)]) ? $this->parents[get_class($this)] : 0;
        $prefix = str_repeat('    ',$count).$this->type.' ';
        $str = '';
        foreach ($this as $child) {
        	$str .= $prefix.mb_trim($child->__toString());
        }
        return $str;
    }

    /**
     * This returns the last child of the list.
     *
     * @return T_Text_ListItem last child
     */
    function getLastChild()
    {
        return end($this->children);
    }

}

