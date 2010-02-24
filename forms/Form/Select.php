<?php
/**
 * Defines the T_Form_Select class.
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
class T_Form_Select extends T_Form_Radio
{

    /**
     * Key value for no input.
     *
     * @var string
     */
    protected $no_input_key = null;

    /**
     * Allow extra argument to specify option for not present.
     *
     * @param array $options  options array
     * @param string $not_present_label  option to add if optional (e.g. 'Please select..')
     * @return T_Form_Select  fluent interface
     */
    function setOptions(array $options,$not_present_label=null)
    {
        if (!is_null($not_present_label)) {
            $this->no_input_key = 'not_present';
            if (array_key_exists($this->no_input_key,$options)) {
                throw new InvalidArgumentException("The key {$this->no_input_key} in options is reserved");
            }
            $options = array($this->no_input_key=>$not_present_label)+$options;
                         // the 'plus' operator merges these two arrays, and importantly *maintains* any numeric keys
                         // while still putting the 'not present' label first. Either array_merge() and array_unshift()
                         // might re-index the array if used here.
        } else {
            $this->no_input_key = null;
        }
        parent::setOptions($options);
        if (!is_null($this->no_input_key) && !$this->getDefault()) {
            $this->setDefault($this->no_input_key);  // not present is default if other no already set
        }
        return $this;
    }

    /**
     * Whether the field is submitted in a particular array cage.
     *
     * @param T_Cage_Array $source  source array to check
     * @return bool  whether a non-zero length value has been submitted
     */
    function isSubmitted(T_Cage_Array $source)
    {
        $submitted = parent::isSubmitted($source);
        if ($submitted) {
            $value = $source->asScalar($this->getFieldname())->uncage();
            if (strcmp($this->no_input_key,$value)===0) $submitted = false;
        }
        return $submitted;
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

    /**
     * Sets the submission as required.
     *
     * @return T_Form_Element  fluent interface
     */
    function setRequired()
    {
        parent::setRequired();
        // if this is definitely required, we want to remove any 'not present'
        // option key from the list of options.
        if ($this->no_input_key) {
            $options = $this->getOptions();
            unset($options[$this->no_input_key]);
            $this->setOptions($options);
        }
        return $this;
    }

}


