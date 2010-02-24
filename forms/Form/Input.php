<?php
/**
 * Contains the T_Form_Input interface.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a input component.
 *
 * @package forms
 */
interface T_Form_Input extends T_CompositeLeaf,T_Visitorable
{

    /**
     * Whether the collection is submitted in a source package ($_GET, $_POST).
     *
     * @param T_Cage_Array $source  source data
     * @return bool  whether the input has been submitted
     */
    function isSubmitted(T_Cage_Array $source);

    /**
     * Validates the component from an input array ($_GET, $_POST).
     *
     * @param T_Cage_Array $source  source data
     * @return T_Form_Input  fluent interface
     */
    function validate(T_Cage_Array $source);

    /**
     * Whether the component is valid.
     *
     * @return bool  whether the component is valid
     */
    function isValid();

    /**
     * Whether the component has been submitted.
     *
     * @return bool  if the component is submitted
     */
    function isPresent();

    /**
     * Sets the fieldname salt.
     *
     * @param string $salt  salt to use for this field
     * @param T_Filter_RepeatableHash $hash  hashing function
     * @return T_Form_Input  fluent interface
     */
    function setFieldnameSalt($salt,T_Filter_RepeatableHash $hash);

    /**
     * Gets the alias.
     *
     * @return string alias
     */
    function getAlias();

    /**
     * Sets the alias.
     *
     * @return T_Form_Input  fluent interface
     */
    function setAlias($alias);

    /**
     * Search for element with a particular name.
     *
     * @param string $alias  alias to search for
     * @return bool|T_Form_Input  element required or false if not found
     */
    function search($alias);

    /**
     * Gets the error that has occurred.
     *
     * @return bool|T_Form_Error  error experienced, or false if no error
     */
    function getError();

    /**
     * Clears any errors (includes children).
     *
     * @return T_Form_Input  fluent interface
     */
    function clearError();

    /**
     * Sets an attribute on a particular member.
     *
     * @param string $name  attribute name
     * @param mixed  $value  attribute value
     * @return T_Form_Element  fluent
     */
    function setAttribute($name,$value);

    /**
     * Gets the value of an attribute.
     *
     * @param string $name   attribute name
     * @return mixed
     */
    function getAttribute($name);

    /**
     * Gets the values of all set attributes.
     *
     * @return array   attribute name=>value pairs
     */
    function getAllAttributes();

}
