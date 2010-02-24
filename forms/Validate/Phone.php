<?php
/**
 * Defines the T_Validate_Phone class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Validates that a string is in a phone number format.
 *
 * This valids a string input is in a valid phone number format. Phone numbers
 * can be formatted in a number of ways, so this is reasonably loose. The rules
 * that must be met are:
 *
 *   - Starts with a plus, digit or opening bracket.
 *   - Ends with a digit
 *   - body can contain digits, brackets, spaces, 'x' or 'ext'.
 *   - between 6 and 20 digits.
 *
 * @package forms
 */
class T_Validate_Phone extends T_Filter_Skeleton
{

    /**
     * Checks string is in a phone number format.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $matches=null; 
        $digits = preg_match_all('/[0-9]/',$value,$matches); // count num of digits
        $regex = '/^[\+0-9\(][0-9\(\) -]+(x|ext)?[0-9\(\) -]*[0-9]$/i';
        if (!preg_match($regex,$value) || $digits<6 || $digits>20) {
            throw new T_Exception_Filter('is not a valid telephone number');
        }
        return $value;
    }

}