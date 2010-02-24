<?php
/**
 * Defines the T_Form_Text class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a user freeform text input.
 *
 * @package forms
 */
class T_Form_Text extends T_Form_Element
{

    /**
     * Sets an attribute on a particular member.
     *
     * @param string $name  attribute name
     * @param mixed  $value  attribute value
     * @return T_Form_Element  fluent
     */
    function setAttribute($name,$value)
    {
        if (strcasecmp($name,'maxlength')===0) {
            $this->setMaxLength($value);  // enforce max length if set
        }
        return parent::setAttribute($name,$value);
    }

    /**
     * Sets maximum permissable length.
     *
     * @param int $max_len  maximum permissable length
     */
    protected function setMaxLength($max_len)
    {
        /* server side filter always at front of queue: if already exists,
           modify it. Otherwise add server side validation rule */
        $first = _first($this->filters);
        if ($first !== false && $first instanceof T_Validate_MaxLength) {
            $first->setMaxLength($max_len);
        } else {
            array_unshift($this->filters,new T_Validate_MaxLength($max_len));
        }
        return $this;
    }

}
