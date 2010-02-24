<?php
/**
 * Defines the T_Validate_WithinNumericRange class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that input falls within a certain numeric range.
 *
 * @package forms
 */
class T_Validate_WithinNumericRange extends T_Filter_Skeleton
{

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
     * @param mixed $min  minimum (null for unlimited)
     * @param mixed $max  maximum (null for unlimited)
     * @param function $filter  The prior filter object
     */
    function __construct($min,$max,$filter=null)
    {
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
            $msg = "is too small. The minimum is {$this->min}";
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->max) && $value>$this->max) {
            $msg = "is too big. The maximum is {$this->max}";
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}