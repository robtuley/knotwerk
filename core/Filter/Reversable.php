<?php
/**
 * Defines the T_Filter_Reversable interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for reversable filter types.
 *
 * Reversable filters can transform one way, and then reverse the transformation
 * to get back to the original data. They can also be chained like normal
 * filters, and indeed used as normal filters.
 *
 * <?php
 * $filter = new SomeFilter(new AnotherFilter());
 * $output = $filter->transform($input);
 * $original = $filter->reverse($output);
 * ?>
 *
 * @package core
 */
interface T_Filter_Reversable extends T_Filter
{

    /**
     * Reverses filter.
     *
     * @param mixed $value  filtered data to reverse
     * @return mixed  original
     */
    function reverse($value);

}