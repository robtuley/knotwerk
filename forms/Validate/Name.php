<?php
/**
 * Defines the T_Validate_Name class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that string is a surname format.
 *
 * This tests that a string is a valid surname format. Letters, dashes, space
 * and apostrophe are permitted. The first and last character must be a letter
 * only.
 *
 * @package forms
 * @license http://knotwerk.com/licence MIT
 */
class T_Validate_Name extends T_Filter_Skeleton
{

    /**
     * Checks string consists of letters, dashes, space or apostrophe only.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $regex = '/^\p{L}[\p{L} \'-]+\p{L}$/u';
        if (!preg_match($regex,$value)) {
            $msg = 'is invalid: only letters, dash, space and apostrophe is permitted';
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}
