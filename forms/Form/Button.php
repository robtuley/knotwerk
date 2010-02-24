<?php
/**
 * Defines the T_Form_Button class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a form button action.
 *
 * @package forms
 */
class T_Form_Button
{

    /**
     * Element alias.
     *
     * @var string
     */
    protected $alias;

    /**
     * Element label.
     *
     * @var string
     */
    protected $label;

    /**
     * Whether element is present or not.
     *
     * @var bool
     */
    protected $is_present = false;

    /**
     * Fieldname salt.
     *
     * @var string
     */
    protected $salt = false;

    /**
     * Fieldname hashing function.
     *
     * @var T_Filter_RepeatableHash
     */
    protected $hash = false;

    /**
     * Create button element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        $this->alias = (string) $alias;
        $this->label = (string) $label;
        if (strlen($alias)==0 || strlen($label)==0) {
            throw new InvalidArgumentException('zero length alias or label');
        }
    }

    /**
     * Whether the action is submitted.
     *
     * @param T_Cage_Array $source  source array to check
     * @return bool  whether the action is present in the source array
     */
    function isSubmitted(T_Cage_Array $source)
    {
        return $source->exists($this->getFieldname());
    }

    /**
     * Validate element user input source.
     *
     * @param T_Cage_Array $source  source array to validate
     * @return T_Form_Element  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $this->is_present = $this->isSubmitted($source);
        return $this;
    }

    /**
     * Whether the element has been submitted.
     *
     * @return bool  if the element is submitted
     */
    function isPresent()
    {
        return $this->is_present;
    }

    /**
     * Sets the fieldname salt.
     *
     * @param string $salt  salt to use for this field
     * @return T_Form_Element  fluent interface
     */
    function setFieldnameSalt($salt,T_Filter_RepeatableHash $hash)
    {
        $this->salt = (string) $salt;
        $this->hash = $hash;
        return $this;
    }

    /**
     * Gets the fieldname of this element.
     *
     * @return string  fieldname
     */
    function getFieldname()
    {
        if ($this->salt && $this->hash) {
            return 'c'._transform($this->alias.$this->salt,$this->hash);
        } else {
            return $this->alias;
        }
    }

    /**
     * Gets the alias.
     *
     * @return string alias
     */
    function getAlias()
    {
        return $this->alias;
    }

    /**
     * Get element label.
     *
     * @param function $f  filter to apply
     * @return string  element label
     */
    function getLabel($f=null)
    {
        return _transform($this->label,$f);
    }

    /**
     * Set element label.
     *
     * @param string $label
     * @return T_Form_Button
     */
    function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

}
