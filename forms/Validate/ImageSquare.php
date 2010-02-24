<?php
/**
 * Defines the T_Validate_ImageSquare class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that image is square.
 *
 * @package forms
 */
class T_Validate_ImageSquare extends T_Filter_Skeleton
{

    /**
     * Checks image is square.
     *
     * @param T_Image_Gd $value  image to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        if ($value->getWidth() !== $value->getHeight()) {
            $msg = "must be square (i.e. width equal to height)";
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}