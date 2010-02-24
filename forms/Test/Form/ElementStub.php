<?php
/**
 * Defines the T_Test_Form_ElementStub class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a input element test stub.
 *
 * @package formTests
 */
class T_Test_Form_ElementStub extends T_Form_Element implements T_Test_Stub
{

    /**
     * Whether the element has been validated.
     *
     * @var bool
     */
    protected $is_validated = false;

    /**
     * Whether the element has been validated.
     *
     * @return bool
     */
    function isValidated()
    {
        return $this->is_validated;
    }

    /**
     * Validate element.
     *
     * @param T_Cage_Array $source  source to validate
     */
    function validate(T_Cage_Array $source)
    {
        $this->is_validated = true;
        return parent::validate($source);
    }

    /**
     * Gets the fieldname salt.
     *
     * @return bool|string  the fieldname salt, or false if not set.
     */
    function getFieldnameSalt()
    {
        return $this->salt;
    }

}