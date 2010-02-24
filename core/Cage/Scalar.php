<?php
/**
 * Defines the T_Cage_Scalar class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Cages insecure data item.
 *
 * This class is used to "cage" an insecure data item to remind developers
 * that it has to be treated with appropriate care. The raw item can be accessed
 * using the uncage() method or filtered using the filter() method. For example:
 * <code>
 * $value = $caged->filter(new SomeFilter())->uncage();
 * </code>
 *
 * @package core
 */
class T_Cage_Scalar
{

    /**
     * Caged data.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Register the data to encapsulate.
     *
     * @param mixed $data  data to encapsulate
     */
    function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Filters the encapsulated data.
     *
     * @param function $filter  filter to apply to data.
     * @return OKT_Caged
     */
    function filter($filter)
    {
        return new T_Cage_Scalar(_transform($this->data,$filter));
    }

    /**
     * Returns the encapsulated data.
     *
     * @return mixed  the caged data
     */
    function uncage()
    {
        return $this->data;
    }

}