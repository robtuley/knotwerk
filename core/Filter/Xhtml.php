<?php
/**
 * Contains the T_Filter_Xhtml class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Escape String for XHTML Filter.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_Xhtml extends T_Filter_Skeleton
{

    /**
     * Escapes string for XHTML output.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        return htmlentities($value,ENT_COMPAT,T_CHARSET);
    }

}