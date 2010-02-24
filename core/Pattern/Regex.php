<?php
/**
 * Contains the T_Pattern_Regex class.
 *
 * @package core 
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * PCRE regular expression.
 *
 * This object is defined by a single PCRE regular expression, and provides
 * various methods to use the encapsulated regex. Note that all methods use case
 * sensitive matching techniques unless the regex specifies otherwise.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Pattern_Regex implements T_Pattern
{

    /**
     * Regular expression string.
     *
     * This variable stores the PCRE regular expression string.
     *
     * @var string
     */
    protected $regex;

    /**
     * Defines the PCRE regular expression.
     *
     * Input must be a valid PCRE regular expression, which will be
     * encapsulated by this class.
     *
     * @param string $regex  PCRE regular expression
     */
    function __construct($regex)
    {
        $this->regex = $regex;
    }

    /**
     * Finds if there is a match.
     *
     * Searches the mixed input for match(es) using the encapsulated regex, and
     * returns whether or not a match was found.
     *
     * @param string $haystack  value to search for matches in
     * @return bool  whether a match was found or not.
     */
    function isMatch($haystack)
    {
        return (bool) preg_match($this->regex,$haystack);
    }

    /**
     * Gets an array of the first matching value with any sub-patterns.
     *
     * Using the regex, this method searches the input for matches and returns
     * an array of the first matching value and any parenthesised sub-patterns.
     * For example,
     * <code>
     * $test  = new T_Pattern_Regex('/t(\w)(\d)t/');
     * $match = $test->getFirstMatch('t3t taat te3t t2at to8t te3t tig');
     * print_r($match);
     * // Array ( [0] => te3t [1] => e [2] => 3 )
     * </code>
     *
     * @param string $haystack  value to search for matches in
     * @return array  array of the first matching value and any sub-patterns,
     *                returns FALSE if no match.
     */
    function getFirstMatch($haystack)
    {
        if (!preg_match($this->regex,$haystack,$match)) {
            $match = false;  // no matches
        }
        return $match;
    }

    /**
     * Gets an array of ALL matching values with any sub-patterns.
     *
     * Using the regex, this method searches the input for matches and returns
     * a 2D array of all matching values and any parenthesised sub-patterns.
     *
     * <code>
     * $test  = new T_Pattern_Regex('/t(\w)(\d)t/');
     * print_r( $test->getAllMatch('t3t taat te3t t2at to8t te3t tig') );
     * // Array (
     * //         [0] => Array ( [0] => te3t [1] => to8t [2] => te3t )
     * //         [1] => Array ( [0] => e    [1] => o    [2] => e    )
     * //         [2] => Array ( [0] => 3    [1] => 8    [2] => 3    )
     * //        )
     * </code>
     *
     * @param string $haystack  value to search for matches in
     * @return array  2D array of all matching values and any sub-patterns,
     *                returns FALSE if no match.
     */
    function getAllMatch($haystack)
    {
        if (!preg_match_all($this->regex,$haystack,$match)) {
            $match = false;  // no matches
        }
        return $match;
    }

    /**
     * Replaces matching values with another value.
     *
     * Uses the regex to replace all matching values in the input with the
     * supplied new value.
     *
     * @param string $replacement value to replace matches with
     * @param string $haystack  value to search for matches in
     * @return string  the modified string
     */
    function replace($replacement,$haystack)
    {
        return preg_replace($this->regex,$replacement,$haystack);
    }

}