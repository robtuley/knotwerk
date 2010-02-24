<?php
/**
 * Contains the T_Validate_ArraySubset class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This checks an array is a subset of an existing array.
 *
 * @package forms
 */
class T_Validate_ArraySubset extends T_Filter_Skeleton
{

    /**
     * Array of possible options.
     *
     * @var array
     */
    protected $options;

    /**
     * Setup maximum length.
     *
     * @param array $options  array of possible options
     * @param function $filter  prior filter object
     */
    function __construct(array $options,$filter=null)
    {
        $this->options = $options;
        parent::__construct($filter);
    }

    /**
     * Checks that each member of the array is a valid option.
     *
     * @param array $value  data to check
     * @return array  normalized data
     * @throws T_Exception_Filter  when data is not a member of options
     */
    protected function doTransform($value)
    {
        $normalised = array();
        foreach ($value as $k => $v) {
        	$opt = array_search($v,$this->options,false);
            if ($opt===false) {
                throw new T_Exception_Filter('contains an invalid option');
            }
            $normalised[$k] = $this->options[$opt];
        }
        return $normalised;
    }

    /**
     * Changes the option array.
     *
     * @param array $options  array of possible options
     */
    function setOptions(array $options)
    {
        $this->options = $options;
    }

}