<?php
/**
 * Defines the T_Validate_ImageAspectRange class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that image falls within a range of aspects (width/height).
 *
 * @package forms
 */
class T_Validate_ImageAspectRange extends T_Filter_Skeleton
{

    /**
     * Min aspect.
     *
     * @var int
     */
    protected $min;

    /**
     * Max aspect.
     *
     * @var int
     */
    protected $max;

    /**
     * Create filter.
     *
     * @param float $min  minimum aspect
     * @param float $max  maximum aspect
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
        $aspect = $value->getWidth()/$value->getHeight();
        if (!is_null($this->min) && $aspect<$this->min) {
            $min = round($this->min,2);
            $msg = "has too small an aspect ratio (width/height). The minimum is $min";
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->max) && $aspect>$this->max) {
            $max = round($this->max,2);
            $msg = "has too large an aspect ratio (width/height). The minimum is $max";
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}