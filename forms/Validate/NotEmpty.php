<?php
/**
 * Defines the T_Validate_NotEmpty class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that array/string is not empty/zero length.
 *
 * @package forms
 * @license http://knotwerk.com/licence MIT
 */
class T_Validate_NotEmpty extends T_Filter_Skeleton
{

    /**
     * Checks array/string is not empty/zero length.
     *
     * @param mixed $value  data to filter
     * @throws T_Exception_Filter  when empty array or zero length string
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        if (empty($value) && (is_array($value) || strlen($value)==0)) {
            throw new T_Exception_Filter('is missing');
        }
        return $value;
    }

}