<?php
/**
 * Contains T_Filter_ArrayMap class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This applies a filter to each member of an array.
 *
 * @package forms
 */
class T_Filter_ArrayMap extends T_Filter_Skeleton
{

    /**
     * Filter to map to each element.
     *
     * @var T_Filter_Skeleton
     */
    protected $map_filter;

    /**
     * Specify filter to map.
     *
     * @param T_Filter $map_filter  filter to map to each element
     * @param function $filter  prior filter object
     */
    function __construct($map_filter,$filter=null)
    {
        $this->map_filter = $map_filter;
        parent::__construct($filter);
    }

    /**
     * Applies filter to each member of array.
     *
     * @param array $value  data to filter
     * @return array  filtered data
     */
    protected function doTransform($value)
    {
		if (!is_array($value)) {
			throw new T_Exception_Filter("$value is not an array");
		}
        $i = 1;
        foreach ($value as &$element) {
            try {
        	   $element = _transform($element,$this->map_filter);
            } catch (T_Exception_Filter $error) {
                $as_ord = new T_Filter_Ordinal();
                $msg = _transform($i,$as_ord).' item '.$error->getMessage();
                throw new T_Exception_Filter($msg);
            }
            $i++;
        }
        return $value;
    }

}
