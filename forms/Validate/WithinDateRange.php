<?php
/**
 * Defines the T_Validate_WithinDateRange class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that input falls within a certain date (UNIX time) range.
 *
 * @package forms
 */
class T_Validate_WithinDateRange extends T_Filter_Skeleton
{

    /**
     * Date format for error messages.
     *
     * @var string
     */
    protected $fmt;

    /**
     * Min.
     *
     * @var int
     */
    protected $min;

    /**
     * Max.
     *
     * @var int
     */
    protected $max;

    /**
     * Create filter.
     *
     * @param string $fmt  date format for error message display
     * @param int $min  minimum (null for unlimited)
     * @param int $max  maximum (null for unlimited)
     * @param function $filter  The prior filter object
     */
    function __construct($fmt,$min,$max,$filter=null)
    {
        $this->fmt = $fmt;
        $this->min = $min;
        $this->max = $max;
        parent::__construct($filter);
    }

    /**
     * Checks value is within range.
     *
     * @param int $value  value to check
     * @return int  filtered value
     */
    protected function doTransform($value)
    {
        if (!is_null($this->min) && $value<$this->min) {
            $msg = "must be after ".date($this->fmt,$this->min);
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->max) && $value>$this->max) {
            $msg = "must be before ".date($this->fmt,$this->max);
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}