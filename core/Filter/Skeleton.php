<?php
/**
 * Contains the T_Filter_Skeleton class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Master class for data filters.
 *
 * @package core
 */
abstract class T_Filter_Skeleton implements T_Filter
{

    /**
     * Prior filter.
     *
     * @var function
     */
    protected $filter;

    /**
     * Register a prior filter.
     *
     * The constructor for the class accepts another filter object that is to
     * be executed before the current filter object. The argument is optional,
     * and a null value indicates no prior filter need be applied.
     *
     * @param function $filter  The prior filter object
     */
    function __construct($filter=null)
    {
        $this->filter = $filter;
    }

    /**
     * Applies filter.
     *
     * This function applies the composite filter to the input.
     *
     * @param mixed $input  value to apply filter to
     * @return mixed  filtered value
     */
    function transform($input)
    {
        return $this->doTransform(_transform($input,$this->filter));
    }

    /**
     * Abstract container for filter operations.
     *
     * This abstract function is defined by specific filter objects that inherit
     * from this class. Such objects need simply to define the filter action in
     * this method.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    abstract protected function doTransform($value);

}
