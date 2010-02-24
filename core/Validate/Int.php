<?php
/**
 * Contains the T_Validate_Int class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Cast to Integer Filter.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Validate_Int extends T_Filter_Skeleton
{

    /**
     * Converts to integer number.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $int = (int) $value;
        if (((string)$int) !== ((string)$value)) {
            throw new T_Exception_Filter('must be a whole number');
        }
        return $int;
    }

}