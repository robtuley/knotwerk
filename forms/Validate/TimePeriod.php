<?php
/**
 * Contains the T_Validate_TimePeriod class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This filter is applied to an input collection and validates 2 fields as
 * a time period (e.g. from date -> to another date).
 *
 * The constraints are:
 *   o Start date must be before or equal to end date.
 *   o If a min time period is specified must be at least this long.
 *   o If a max time period is specified must be less than or equal to this long.
 *
 * @package forms
 */
class T_Validate_TimePeriod extends T_Filter_Skeleton
{

    /**
     * Start fieldname.
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
     * Minimum time period length (seconds).
     *
     * @var int
     */
    protected $min = null;

    /**
     * Maximum time period length (seconds).
     *
     * @var int
     */
    protected $max = null;

    /**
     * Setup filter.
     *
     * @param string $start_field  start time/date field
     * @param string $end_field  end time/date field
     * @param int $min  optional minium time period
     * @param int $max  optional max time period
     * @param function $filter  prior filter object
     */
    function __construct($start_field,$end_field,$min=null,$max=null,$filter=null)
    {
        $this->start = $start_field;
        $this->end = $end_field;
        $this->min = $min;
        $this->max = $max;
        parent::__construct($filter);
    }

    /**
     * Checks that if both fields are present and value, time period meets spec.
     *
     * @param T_Form_Group $value  collection to examine
     * @return void
     * @throws T_Exception_Filter  if time period is not value
     */
    protected function doTransform($value)
    {
        $start = $value->search($this->start);
        $end = $value->search($this->end);
        if (!$start || !$end) {
            $msg = "does not contain $this->start or $this->end";
            throw new InvalidArgumentException($msg);
        }
        // if either field already has errors, or if not present,
        // no validation is required.
        if (!$start->isValid() || !$start->isPresent() ||
            !$end->isValid() || !$end->isPresent()) return;

        $period = $end->getValue()-$start->getValue();
        if ($period<0) {
            $msg = "{$end->getLabel()} must be after {$start->getLabel()}";
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->min) && $period<$this->min) {
            $f = new T_Filter_HumanTimePeriod();
            $msg = "The period between {$start->getLabel()} and {$end->getLabel()} must be at least "._transform($this->min,$f);
        }
        if (!is_null($this->max) && $period>$this->max) {
            $f = new T_Filter_HumanTimePeriod();
            $msg = "The period between {$start->getLabel()} and {$end->getLabel()} may be a maximum of "._transform($this->max,$f);
        }
    }

}