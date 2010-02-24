<?php
/**
 * Defines the T_Form_Radio class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a multiple option single input choice.
 *
 * @package forms
 */
class T_Form_Radio extends T_Form_Element
{

    /**
     * Options available to the user.
     *
     * @var array
     */
    protected $options = array();

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
        if ($first !== false && $first instanceof T_Validate_ArrayMember) {
            $first->setOptions(array_keys($this->options));
        } else {
            $f = new T_Validate_ArrayMember(array_keys($this->options));
            array_unshift($this->filters,$f);
        }
        $default = $this->getDefault();
        if ($default && !isset($this->options[$default])) {
            $this->clearDefault(); // existing default not in new options
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

    /**
     * Set default.
     *
     * The default value here should be one of the existing list of options.
     *
     * @param array $value default value.
     * @return OKT_FormElement  fluent interface
     */
    function setDefault($value)
    {
        if (!array_key_exists($value,$this->options)) {
            throw new InvalidArgumentException("default $value is not in options");
        }
        return parent::setDefault($value);
    }

}