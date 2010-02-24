<?php
/**
 * Contains the T_Validate_ArrayMember class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This checks a scalar is a member of an existing array.
 *
 * @package forms
 */
class T_Validate_ArrayMember extends T_Filter_Skeleton
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
     * Checks that the data is not over a maximum length.
     *
     * @param mixed $value  data to check
     * @return mixed  normalized data
     * @throws T_Exception_Filter  when data is not a member of options
     */
    protected function doTransform($value)
    {
        $key = array_search($value,$this->options,false);
        if ($key===false) {
            throw new T_Exception_Filter('is not a permitted value');
        }
        return $this->options[$key];
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