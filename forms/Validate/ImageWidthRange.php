<?php
/**
 * Defines the T_Validate_ImageWidthRange class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that image falls within a range of widths.
 *
 * @package forms
 */
class T_Validate_ImageWidthRange extends T_Filter_Skeleton
{

    /**
     * Min width (pixels).
     *
     * @var int
     */
    protected $min;

    /**
     * Max width (pixels).
     *
     * @var int
     */
    protected $max;

    /**
     * Create filter.
     *
     * @param int $min  minimum width
     * @param int $max  maximum width
     * @param function $filter  The prior filter object
     */
    function __construct($min,$max,$filter=null)
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($filter);
    }

    /**
     * Checks image is within width range.
     *
     * @param T_Image_Gd $value  image to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        if (!is_null($this->min) && $value->getWidth()<$this->min) {
            $msg = "is too small an image. The minimum width is {$this->min} pixels";
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->max) && $value->getWidth()>$this->max) {
            $msg = "is too big an image. The maximum width is {$this->max} pixels";
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}