<?php
/**
 * Contains the T_Validate_DependentFields class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This filter is applied to an input collection validates the presence of dependent fields.
 *
 * Dependent fields are those that rely on the presence of the other. For example, if the first
 * line of an address is present, so too must the postcode and city. If the postcode is present,
 * so too must the first line. In other words, these are optional fields as a group, but if 1
 * field id present, all fields must be present.
 *
 * @package forms
 */
class T_Validate_DependentFields extends T_Filter_Skeleton
{

    /**
     * Array of fieldnames.
     *
     * @var string[]
     */
    protected $fields;

    /**
     * Setup filter.
     *
     * @param array $fields  array of field names
     * @param function $filter  prior filter object
     */
    function __construct(array $fields,$filter=null)
    {
        if (count($fields)<2) {
            throw new InvalidArgumentException("Filter only takes action if more than 1 field provided");
        }
        $this->fields = $fields;
        parent::__construct($filter);
    }

    /**
     * Checks that if any fields are present, all are present.
     *
     * @param T_Form_Group $value  collection to process
     * @return void
     */
    protected function doTransform($value)
    {
        $fields = array();
        $any_present = false;
        foreach ($this->fields as $name) {
            $f = $value->search($name);
            if (!$f) {
                $msg = "does not contain $name field";
                throw new InvalidArgumentException($msg);
            }
            if (!$any_present) $any_present = $f->isPresent();
            $fields[] = $f;
        }
        if ($any_present) {
            foreach ($fields as $f) {
                if ($f->isValid() && !$f->isPresent()) {
                    $f->setError(new T_Form_Error('is missing'));
                }
            }
        }
    }

}