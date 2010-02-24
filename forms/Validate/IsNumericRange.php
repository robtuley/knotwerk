<?php
/**
 * Defines the T_Validate_IsNumericRange class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Validates that two fields together form a numeric range (start < end).
 *
 * This can be used for date ranges (UNIX integer dates), price ranges,
 * integer ranges, etc.
 *
 * @package forms
 */
class T_Validate_IsNumericRange extends T_Filter_Skeleton
{

    /**
     * start fieldname.
     *
     * @var string
     */
    protected $start;

    /**
     * End fieldname.
     *
     * @var string
     */
    protected $end;

    /**
     * Setup filter.
     *
     * @param string $start_field  start fieldname
     * @param string $end_field  end fieldname
     * @param function $filter  prior filter object
     */
    function __construct($start_field,$end_field,$filter=null)
    {
        $this->start = $start_field;
        $this->end = $end_field;
        parent::__construct($filter);
    }

    /**
     * Checks that if both are present, end int is > start int.
     *
     * @param T_Form_Group $value  collection to examine
     * @return void
     * @throws T_Exception_Filter  when end date not correct
     */
    protected function doTransform($value)
    {
        $start = $value->search($this->start);
        $end = $value->search($this->end);
        if ($start===false || false===$end) {
            $msg = "does not contain $this->start and $this->end";
            throw new InvalidArgumentException($msg);
        }
        if (!$start->isValid() || !$end->isValid()) return;
        if ($start->isPresent() && $end->isPresent()) {
            if ($start->getValue() > $end->getValue()) {
                $msg = 'must be larger than '.$start->getLabel();
                $end->setError(new T_Form_Error($msg));
            }
        }
    }

}