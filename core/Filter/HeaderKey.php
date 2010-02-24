<?php
/**
 * Contains the T_Filter_HeaderKey class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Normalises the case of a header key.
 *
 * @package core
 */
class T_Filter_HeaderKey extends T_Filter_Skeleton
{

    /**
     * Normalises HTTP Header key case.
     *
     * @param string $value  data to filter
     * @return string  valid filename
     */
    protected function doTransform($value)
    {
        $tmp = explode('-',$value);
        foreach ($tmp as &$item) $item = ucfirst(strtolower($item));
        return implode('-',$tmp);
    }

}