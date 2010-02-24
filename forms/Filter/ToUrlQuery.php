<?php
/**
 * Contains the T_Filter_ToUrlQuery class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Convert array to URL query string.
 *
 * @package forms
 */
class T_Filter_ToUrlQuery implements T_Filter_Reversable
{

    /**
     * Convert array to query string.
     *
     * @param array $data  data to convert to string
     * @return string  URL data
     */
    function transform($data)
    {
        return http_build_query($data,null,'&');
    }

    /**
     * Convert query string back to array of values.
     *
     * @param string $query  query string
     * @return array  data
     */
    function reverse($query)
    {
        $data = array();
        parse_str($query,$data);
        if (get_magic_quotes_gpc()) { // affected by magic_quotes settings
            $f = new T_Filter_NoMagicQuotes();
            $data = _transform($data,$f);
        }
        return $data;
    }

}