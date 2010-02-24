<?php
/**
 * Contains the T_Filter_UrlPath class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * URL Path Segments Filter.
 *
 * This filter transforms relatively addressed URL strings (no scheme or domain
 * prefix) into an array of path segment strings. For example:
 *
 * <?php
 * $filter = new T_Filter_UrlPath();
 * $path = $filter->transform($relUrl);
 * // /some/path --> array('some','path')
 * // /some/path#anchor --> array('some','path')
 * // /some/path?name=value --> array ('some','path')
 * // /some/path/filename.php --> array ('some','path','filename.php')
 * ?>
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_UrlPath extends T_Filter_Skeleton
{

    /**
     * Converts relative URL to path segments.
     *
     * @param string $value  data element value
     * @return array  key and lowercase element
     */
    protected function doTransform($value)
    {
        // strip parameters
        $pos = strpos($value,'?');
        if ($pos !== false) {
            $value = substr($value,0,$pos);
        }
        // strip anchors
        $pos = strpos($value,'#');
        if ($pos !== false) {
            $value = substr($value,0,$pos);
        }
        // trim any trailing or starting slashes
        $value = trim($value,'/');
        // explode to segments
        if (strlen($value)>0) {
            $value = explode('/',$value);
        } else {
            $value = array();
        }
        // unencode
        foreach ($value as &$segment) {
        	$segment = rawurldecode($segment);
        }
        return $value;
    }

}