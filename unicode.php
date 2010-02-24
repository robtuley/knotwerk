<?php
/**
 * Defines UTF-8 character functions.
 *
 * This file defines a set of unicode-safe functions for PHP5 with mbstring.
 * Note that since every complete character sequence in a UTF-8 string is
 * unique (cannot be mistaken as part of a longer sequence) functions such as
 * explode, str_replace, substr_replace, etc. are perfectly safe to use as long
 * as the input arguments are well-formed UTF-8.
 *
 * The inspiration for most of these functions comes from DocuWiki and also the
 * rather impressive UTF8 library Harry Fuecks put together. To finish off and
 * ensure output and input is encoded in UTF8, we should:
 *
 * <code>
 * header('Content-type: text/html; charset=UTF-8');
 * </code>
 *
 * And also include in the XHTML page:
 *
 * <samp>
 * <meta http-equiv="Content-type" value="text/html; charset=UTF-8" />
 * </samp>
 *
 * Remember also to setup the database as 'UTF-8'!
 *
 * <samp>
 * CREATE DATABASE db_name
 * CHARACTER SET utf8
 * DEFAULT CHARACTER SET utf8
 * COLLATE utf8_general_ci
 * DEFAULT COLLATE utf8_general_ci;
 * </samp>
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 * @see http://sourceforge.net/projects/phputf8
 * @see http://www.phpwact.org/php/i18n/utf-8
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Character encoding to use.
 *
 * @global string T_CHARSET
 */
define('T_CHARSET','UTF-8');
mb_internal_encoding(T_CHARSET);
mb_language('uni');  /* sets email encoding to UTF-8 */

/**
 * Checks if a str contains 1 bit ascii only.
 *
 * @param string $str  string to check
 * @return bool  whether the string has any ascii characters
 */
function mb_is_ascii($str)
{
    return ( strlen($str) === mb_strlen($str) );
}

/**
 * Strips any non-ascii values from a string.
 *
 * @param string $str  string to operate on
 * @return string  string with any non-ascii characters removed
 */
function mb_reduce_to_ascii($str)
{
    $ascii = '';
    for($i=0; $i<strlen($str); $i++){
        if (ord($str{$i})<128) {
            $ascii .= $str{$i};
        }
    }
    return $ascii;
}

/**
 * Unicode aware replacement for substr_replace.
 *
 * If arbitrary start and length arguments are supplied, could corrupt a
 * UTF-8 string as can 'chop' a string in the wrong byte-positions.
 *
 * @see substr_replace
 * @param mixed $string  string to operate on
 * @param mixed $replacement  replacement string
 * @param int $start  replacing begins at start'th offset into string
 * @param int $length  length of the string portion to replace
 * @return string  result string with substring replaced
 */
function mb_substr_replace($string,$replacement,$start,$length=0)
{
    $ret = '';
    if ($start>0) $ret .= mb_substr($string,0,$start);
    $ret .= $replacement;
    $ret .= mb_substr($string,$start+$length);
    return $ret;
}

/**
 * Unicode aware replacement for strcasecmp.
 *
 * @see strcasecmp
 * @param string $str1  first string to compare
 * @param string $str2  second string to compare
 * @return int  binary safe case-insensitive string comparison
 */
function mb_strcasecmp($str1,$str2)
{
    $str1 = mb_strtolower($str1);
    $str2 = mb_strtolower($str2);
    return strcmp($str1,$str2);
}

if (!function_exists('mb_stristr')) {
    /**
     * Unicode aware replacement for stristr.
     *
     * In PHP 5.2.0+, this is included in the standard mbstring library, so we only declare this
     * function if it is not present already.
     *
     * @see stristr
     * @param string $haystack  string to search within
     * @param string $needle  string to search for
     * @return string  all of haystack from the first occurrence of needle to end
     */
    function mb_stristr($haystack,$needle)
    {
        $pos = mb_strpos(mb_strtolower($haystack),mb_strtolower($needle));
        if ($pos === false) {
            return false;
        } else {
            return mb_substr($haystack,$pos);
        }
    }
}

/**
 * Unicode aware replacement for ucfirst.
 *
 * @see ucfirst
 * @param string $str  string to operate on
 * @return string  copy of string with first letter capitalised
 */
function mb_ucfirst($str)
{
    return mb_strtoupper(mb_substr($str,0,1)).mb_substr($str,1);
}

/**
 * Unicode aware replacement for str_ireplace.
 *
 * Note that str_replace works absolutely fine with UTF8 strings, as long as the
 * arguments are well-formed, since every complete character sequence in a UTF-8
 * string is unique (cannot be mistaken as part of a longer sequence).
 *
 * @see str_ireplace
 * @param mixed $search  string to search for
 * @param mixed $replace  string to be used as a replacement
 * @param mixed $subject  subject of search and replace
 * @return mixed  result of serach-and-replace (array or string)
 */
function mb_str_ireplace($search,$replace,$subject)
{
    if (!is_array($search)) {
        if (strlen($search)==0) {
            return $subject;  // nothing to replace
        }
        $search = '#'.preg_quote($search,'#').'#ui';
    } else {
        foreach ($search as $k => $v) {
            if (strlen($v)==0) {
                unset($search[$k]);
            } else {
                $search[$k] = '#'.preg_quote($v,'#').'#ui';
            }
        }
    }
    return preg_replace($search,$replace,$subject);
}

/**
 * Unicode aware replacement for ltrim.
 *
 * Trimming can corrupt a Unicode string by replacing single bytes from a
 * multi-byte sequence. Used in a default manner, ltrim is UTF-8 safe, but
 * with the optional charlist variable specified it can corrupt strings.
 *
 * @see ltrim
 * @param string $str  string to trim
 * @param string $charlist  list of characters to trim
 * @return string  trimmed string
 */
function mb_ltrim($str,$charlist='')
{
    if (strlen($charlist)==0) {
        return ltrim($str);
    } else {
        $charlist = preg_quote($charlist,'#');
        return preg_replace('#^['.$charlist.']+#u','',$str);
    }
}

/**
 * Unicode aware replacement for rtrim.
 *
 * @see rtrim
 * @param string $str  string to trim
 * @param string $charlist  list of characters to trim
 * @return string  trimmed string
 */
function mb_rtrim($str,$charlist='')
{
    if (strlen($charlist)==0) {
        return rtrim($str);
    } else {
        $charlist = preg_quote($charlist,'#');
        return preg_replace('#['.$charlist.']+$#u','',$str);
    }
}

/**
 * Unicode aware replacement for trim.
 *
 * @see trim
 * @param string $str  string to trim
 * @param string $charlist  list of characters to trim
 * @return string  trimmed string
 */
function mb_trim($str,$charlist='')
{
    if (strlen($charlist)==0) {
        return trim($str);
    } else {
        return mb_ltrim(mb_rtrim($str,$charlist),$charlist);
    }
}

/**
 * str_replace with a hard limit.
 *
 * Emulates the str_replace function, but enforces an upper limit on the number
 * of times the serach term is replaced.
 *
 * @param mixed $search  terms to search for
 * @param mixed $replace  terms to use as replacements
 * @param string $subject  subject to search in
 * @param int $times  maximum number of times a replacement can occur
 * @return string  modified subject string
 */
function str_replace_count($search,$replace,$subject,$times) {
    $subject_original = $subject;
    $len = mb_strlen($search);
    $pos = 0;
    for ($i=1;$i<=$times;$i++) {
        $pos = mb_strpos($subject,$search,$pos);
        if ($pos!==false) {
            $subject =  mb_substr($subject_original,0,$pos);
            $subject .= $replace;
            $subject .= mb_substr($subject_original,$pos+$len);
            $subject_original = $subject;
        } else {
            break;
        }
    }
    return $subject;
}