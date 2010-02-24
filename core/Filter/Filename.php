<?php
/**
 * Contains the T_Filter_Filename class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This checks the data is a valid filename.
 *
 * Valid characters are alpha-numeric, -, _ and .
 *
 * @package core
 */
class T_Filter_Filename extends T_Filter_Skeleton
{

    /**
     * Checks that the data is a valid filename.
     *
     * @param string $value  data to filter
     * @return string  valid filename
     * @throws T_Exception_Filter  if the input is not a valid date
     */
    protected function doTransform($value)
    {
        $regex = '/^[A-Z0-9\._-]+$/i';
        if (!preg_match($regex,$value)) {
            throw new T_Exception_Filter("Invalid filename $value");
        }
        return (string) $value;
    }

}