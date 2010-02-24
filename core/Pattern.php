<?php
/**
 * Contains the Pattern interface.
 *
 * This page holds the pattern interface - a prescriptive format for all pattern
 * matching classes.
 *
 * @package core 
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for any pattern-matching classes.
 *
 * This interface is applied to any pattern-matching classes, such as regex
 * classes.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
interface T_Pattern {

    /**
     * Finds if there is a match.
     *
     * Searches the mixed input for matches using its native pattern-matching
     * method, and returns the boolean result.
     *
     * @param mixed $haystack  value to search for matches in
     * @return bool  whether match(s) were found
     */
    function isMatch($haystack);

    /**
     * Gets the first matching value.
     *
     * Using the native pattern-matching method of the class, returns the first
     * matching value.
     *
     * @param mixed $haystack  value to search for matches in
     * @return array  first matching value (position 0) and any sub-matches in
     *                the pattern definition or FALSE for no matches
     */
    function getFirstMatch($haystack);

    /**
     * Gets all the matching values.
     *
     * Using the native pattern-matching method of the class, returns the all
     * the matching values along with any sub-pattern matches.
     *
     * @param mixed $haystack  value to search for matches in
     * @return array  2D array of matches and any sub-pattern matches
     */
    function getAllMatch($haystack);

    /**
     * Replaces matching values with another value.
     *
     * Using the native pattern-matching method of the class, this method
     * replaces all matching values in the input with a new value.
     *
     * @param mixed $replacement  value to replace matches with
     * @param mixed $haystack  value to search for matches in
     * @return mixed  the modified value
     */
    function replace($replacement,$haystack);

}