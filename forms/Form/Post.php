<?php
/**
 * Defines the T_Form_Post class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a form to perform an action.
 *
 * @package forms
 */
class T_Form_Post extends T_Form_Container
{

    /**
     * Create POST form.
     *
     * In a POST form it is essential that the form realises it is being submitted.
     * Therefore a 'default' action is added to the form to make this submission
     * explicit always.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        parent::__construct($alias,$label);
        $this->addAction(new T_Form_Button($alias,$label));
    }

    /**
     * Gets the submission method type.
     *
     * @return string  'get' method for resource retrieval
     */
    function getMethod()
    {
        return 'post';
    }

    /**
     * Validates the form.
     *
     * Note that is it important here to only validate the main form body if
     * there is an action present (one is always created by default). This
     * prevents validation when an alternative form on a page has been
     * submitted.
     *
     * @param T_Cage_Array $source  source data
     * @return T_Form_Group  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $is_action = false;
        foreach ($this->action as $button) {
        	$button->validate($source);
        	if ($button->isPresent()) {
        	    $is_action = true;
        	    break;
        	} /* only 1 action ever present */
        }
        if ($is_action) parent::validate($source);
        return $this;
    }

}