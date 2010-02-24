<?php
/**
 * Contains the T_Filter_Ordinal class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Converts integer to ordinal like 1st, 2nd, 3rd.
 *
 * @package core
 */
class T_Filter_Ordinal extends T_Filter_Skeleton
{

    /**
     * Converts integer to ordinal like 1st, 2nd, 3rd.
     *
     * @param int $value  data to filter
     * @return string  filtered value
     */
    protected function doTransform($value)
    {
        $value = (int) $value;
        $last = substr($value,-1,1);
        $pair = substr($value,-2,2);
        if ($pair==11 || $pair==12 || $pair==13) {
            $suffix = 'th';
        } elseif ($last==1) {
            $suffix = 'st';
        } elseif ($last==2) {
            $suffix = 'nd';
        } elseif ($last==3) {
            $suffix = 'rd';
        } else {
            $suffix = 'th';
        }
        return $value.$suffix;
    }

}