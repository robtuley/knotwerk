<?php
/**
 * Contains the T_Test_Filter_Failure class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test Filter that always fails.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_Failure extends T_Filter_Skeleton implements T_Test_Stub
{

    /**
     * Throws filter exception.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     * @throws T_Exception_Filter
     */
    protected function doTransform($value)
    {
        throw new T_Exception_Filter();
    }

}