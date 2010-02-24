<?php
/**
 * Contains the T_Filter_DateStr class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Converts an integer number into a date string.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_DateStr extends T_Filter_Skeleton
{
    /**
     * Date format.
     *
     * @var string
     */
    protected $fmt;

    /**
     * Create filter.
     *
     * @param string $fmt  date format
     * @param function $filter  The prior filter object
     */
    function __construct($fmt,$filter=null)
    {
        $this->fmt = $fmt;
        parent::__construct($filter);
    }

    /**
     * Converts UNIX timestamps to date string.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        return date($this->fmt,$value);
    }

}