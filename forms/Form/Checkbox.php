<?php
/**
 * Defines the T_Form_Checkbox class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a multiple option multiple input choice.
 *
 * @package forms
 */
class T_Form_Checkbox extends T_Form_Element
{

    /**
     * Options available to the user.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Create form element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        parent::__construct($alias,$label);
        $this->as_scalar = false; /* expect an array */
        $this->clearDefault();
    }

    /**
     * Set default.
     *
     * The default value here is an array of values that should be checked by
     * default. On input, these default values are checked to see they are
     * already options.
     *
     * @param array $value default value.
     * @return OKT_FormElement  fluent interface
     */
    function setDefault($value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('default required as array');
        }
        $intersect = array_intersect(array_keys($this->getOptions()),$value);
        if (count($intersect) !== count($value)) {
            throw new InvalidArgumentException('default values not in options');
        }
        return parent::setDefault(array_values($intersect));
    }

    /**
     * Sets the list of options.
     *
     * Each user choosable option has a label and a return value. In this method
     * these are set by passing an array where the array values are the labels,
     * and the keys are the return value.
     *
     * @param array $options  user choosable options
     */
    function setOptions(array $options)
    {
        $this->options = $options;
        /* server side filter always at front of queue: if already exists,
           modify it. Otherwise add server side validation rule */
        reset($this->filters);
        $first = current($this->filters);
        if ($first !== false && $first instanceof T_Validate_ArraySubset) {
            $first->setOptions(array_keys($this->options));
        } else {
            $f = new T_Validate_ArraySubset(array_keys($this->options));
            array_unshift($this->filters,$f);
        }
        return $this;
    }

    /**
     * Gets options available.
     *
     * @return array  user options
     */
    function getOptions()
    {
        return $this->options;
    }

}