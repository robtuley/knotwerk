<?php
/**
 * Defines the T_Test_Form_CollectionFilterStub class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test filter for input collection.
 *
 * @package formTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Form_CollectionFilterStub extends T_Filter_Skeleton implements T_Test_Stub
{

    /**
     * Last filtered value.
     *
     * @var mixed
     */
    protected $value = null;

    /**
     * Dummy filter.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $this->value = $value;
        return $value;
    }

    /**
     * Retrieves filter value.
     *
     * @return mixed
     */
    function getFilterValue()
    {
        return $this->value;
    }

}