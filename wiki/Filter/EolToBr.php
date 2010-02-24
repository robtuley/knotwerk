<?php
/**
 * Contains the T_Filter_EolToBr class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Converts line breaks --> <br /> tags.
 *
 * @package wiki
 */
class T_Filter_EolToBr extends T_Filter_Skeleton
{

    /**
     * Converts line breaks --> <br /> tags.
     *
     * @param string $value  XHTML string with line breaks
     * @return string  XHTML string with <br /> tags
     */
    protected function doTransform($value)
    {
        $lf = '/(?:\r\n|\n|\x0b|\r|\f|\x85)/';
        return preg_replace($lf,'<br />',$value);
    }

}