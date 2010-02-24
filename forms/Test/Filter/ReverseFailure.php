<?php
/**
 * Contains the T_Test_Filter_ReverseFailure class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test Filter that always fails on reverse transform.
 *
 * @package formTests
 */
class T_Test_Filter_ReverseFailure implements T_Filter_Reversable,T_Test_Stub
{

    /**
     * Does nothing.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    function transform($value)
    {
        return $value;
    }

    /**
     * Fails.
     *
     * @param mixed $value  data to filter
     * @throws T_Exception_Filter
     */
    function reverse($value)
    {
        throw new T_Exception_Filter();
    }

}