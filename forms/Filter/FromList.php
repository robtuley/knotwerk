<?php
/**
 * Defines the T_Filter_FromList class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Separates a comma separated list into components.
 *
 * @package forms
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_FromList extends T_Filter_Skeleton
{

    /**
     * Divides comma separated list into array of components.
     *
     * @param mixed $value  data to filter
     * @return array  list components
     */
    protected function doTransform($value)
    {
        $bits = explode(',',$value);
        for ($i=0,$count=count($bits); $i<$count; $i++) {
            $bits[$i] = trim($bits[$i]);
            if (strlen($bits[$i])==0) {
                unset($bits[$i]);
            }
        }
        return array_values($bits);
    }

}