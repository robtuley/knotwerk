<?php
/**
 * Defines the T_Validate_ImageHeightRange class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that image falls within a range of heights.
 *
 * @package forms
 */
class T_Validate_ImageHeightRange extends T_Filter_Skeleton
{

    /**
     * Min height (pixels).
     *
     * @var int
     */
    protected $min;

    /**
     * Max height (pixels).
     *
     * @var int
     */
    protected $max;

    /**
     * Create filter.
     *
     * @param int $min  minimum height
     * @param int $max  maximum height
     * @param function $filter  The prior filter object
     */
    function __construct($min,$max,$filter=null)
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($filter);
    }

    /**
     * Checks image is within height range.
     *
     * @param T_Image_Gd $value  image to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        if (!is_null($this->min) && $value->getHeight()<$this->min) {
            $msg = "is too small an image. The minimum height is {$this->min} pixels";
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->max) && $value->getHeight()>$this->max) {
            $msg = "is too big an image. The maximum height is {$this->max} pixels";
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}