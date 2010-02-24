<?php
/**
 * Defines the T_Validate_Letters class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that string contains letters only.
 *
 * @package forms
 * @license http://knotwerk.com/licence MIT
 */
class T_Validate_Letters extends T_Filter_Skeleton
{

    /**
     * Checks string consists of letters only.
     *
     * @see http://unicode.org/reports/tr18/
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $regex = '/^\p{L}+$/u';
        if (!preg_match($regex,$value)) {
            throw new T_Exception_Filter('must be letters only');
        }
        return $value;
    }

}
