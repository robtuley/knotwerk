<?php
/**
 * Contains the T_Filter_NoMagicQuotes class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Remove Magic Quotes Filter.
 *
 * This class can be applied to an array if magic quotes is turned on to
 * recursively strip all slashes from the array values. Note that the
 * filter does not detect itself whether magic quotes is turned on or off.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_NoMagicQuotes extends T_Filter_Skeleton
{

    /**
     * Recusively strip slashes from keys and values.
     *
     * Note that PHP adds slashes to both keys and values, so to thoroughly
     * clean the input array we must also strip slashes from the keys. This
     * can change the order of the array.
     *
     * @param mixed $value  value to strip slashes from.
     * @return mixed  stripped slashes data
     */
    protected function doTransform($value)
    {
        if (is_array($value)) {
            $data = array();
            foreach ($value as $key => $val) {
                $key = stripslashes($key);
                $val = is_array($val) ? $this->doTransform($val) : stripslashes($val);
                $data[$key] = $val;
            }
            return $data;
        } else {
            return stripslashes($value);
        }
    }

}
