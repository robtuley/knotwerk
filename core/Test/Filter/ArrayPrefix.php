<?php
/**
 * Contains the T_Test_Filter_ArrayPrefix class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test Filter.
 *
 * This is a filter that is used in the unit testing scheme and it simply adds
 * the word 'Tested' to the end of every string in the array passed in.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_ArrayPrefix extends T_Filter_Skeleton implements T_Test_Stub
{

    /**
     * String suffix to add.
     *
     * @var string
     */
    protected $suffix;

    /**
     * Create filter.
     *
     * @param string $suffix  string suffix to add to value
     * @param function $filter  The prior filter object
     */
    function __construct($suffix='Tested',$filter=null)
    {
        $this->suffix = $suffix;
        parent::__construct($filter);
    }

    /**
     * Adds 'Tested' string to end of value.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $out = array();
        foreach ($value as $key => $content) {
        	$out[$key] = $content.$this->suffix;
        }
        return $out;
    }

}