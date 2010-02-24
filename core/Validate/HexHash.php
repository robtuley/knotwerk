<?php
/**
 * Contains the T_Validate_HexHash class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This checks the data is a 32 character hexadecimal hash.
 *
 * @package core
 */
class T_Validate_HexHash extends T_Filter_Skeleton
{

    /**
     * Checks that the data is a valid hexadecimal hash (32 characters long).
     *
     * @param string $value  data to filter
     * @return string  hex hash
     * @throws T_Exception_Filter  if the input is not a valid hex hash
     */
    protected function doTransform($value)
    {
        if (!ctype_xdigit($value) || strlen($value)!==32) {
            throw new T_Exception_Filter("Invalid hexadecimal 32-char hash $value");
        }
        return $value;
    }

}