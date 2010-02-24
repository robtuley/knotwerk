<?php
/**
 * Contains the T_Validate_Confirm class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This filter is applied to an input collection and validates 2 fields as
 * having equal values (e.g. a confirm field)
 *
 * @package forms
 */
class T_Validate_Confirm extends T_Filter_Skeleton
{

    /**
     * Master fieldname.
     *
     * @var string
     */
    protected $master;

    /**
     * Confirm fieldname.
     *
     * @var string
     */
    protected $confirm;

    /**
     * Setup filter.
     *
     * @param string $master  master fieldname
     * @param string $confirm  confirmation field
     * @param function $filter  prior filter object
     */
    function __construct($master,$confirm,$filter=null)
    {
        $this->master = $master;
        $this->confirm = $confirm;
        parent::__construct($filter);
    }

    /**
     * Checks that if the master is present, so too is the confirmation.
     *
     * @param T_Form_Group $value  collection to examine
     * @return void
     * @throws T_Exception_Filter  when data > max length
     */
    protected function doTransform($value)
    {
        $master = $value->search($this->master);
        $confirm = $value->search($this->confirm);
        if (!$master || !$confirm) {
            $msg = "does not contain $this->master or $this->confirm";
            throw new InvalidArgumentException($msg);
        }
        // if either field already has errors, let these be corrected first ...
        if (!$master->isValid() || !$confirm->isValid()) return;
        // ... otherwise check the values are equal
        if ($confirm->getValue() != $master->getValue()) {
            $msg = 'does not match the value in '.$master->getLabel();
            $confirm->setError(new T_Form_Error($msg));
        }
    }

}