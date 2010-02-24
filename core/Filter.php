<?php
/**
 * Defines the T_Filter interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for filter types.
 *
 * All data filters can accept and transform an input. They can also be piped
 * through each other using the decorator pattern.
 *
 * <code>
 * $filter = new SomeFilter(new AnotherFilter());
 * $output = $filter->transform($input);
 * </code>
 *
 * @package core
 */
interface T_Filter
{

    /**
     * Applies filter.
     *
     * @param mixed $value  value to apply filter to
     * @return mixed  filtered value
     */
    function transform($value);

}