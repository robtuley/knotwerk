<?php
/**
 * Contains the T_Filter_ControllerClassname class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Transforms a URL path segment to a controller classname.
 *
 * This filter transforms a pathname segment into a controller classname. It
 * lowercases the pathname segment, explodes it on +,-,_ or space and then joins
 * the array ucfirst-ing each part.
 *
 * <?php
 * $mapping = new T_Filter_ControllerClassname();
 * $classname = $mapping->transform('edit-article');
 * // $classname is 'EditArticle'
 * ?>
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_ControllerClassname extends T_Filter_Skeleton
{

    /**
     * Converts a string to a classname.
     *
     * @param string $value  URL segment to transform
     * @return string  classname
     */
    protected function doTransform($value)
    {
        // split on space,-,_ or +
        $delimit = '/[\s\+_-]/u';  // u to treat as UTF-8
        $words   = preg_split($delimit,mb_strtolower($value),-1,
                              PREG_SPLIT_NO_EMPTY);  // skips empty
        // ucfirst each word and join..
        $words = array_map('mb_ucfirst',$words);
        return join('',$words);
    }

}