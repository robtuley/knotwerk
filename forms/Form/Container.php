<?php
/**
 * Defines the T_Form_Container class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a form collection of input elements.
 *
 * @package forms
 */
abstract class T_Form_Container extends T_Form_Group
{

    /**
     * Sets the URL to forward to after submission.
     *
     * @var T_Url
     */
    protected $forward = null;

    /**
     * Possible form actions button options.
     *
     * @var array
     */
    protected $action = array();

    /**
     * Sets the forward location.
     *
     * The forward location of a form is the serving page itself by default.
     * For GET form submissions, the forward URL is where the from submission
     * is sent. For POST forms, the data is submitted to the same page, actioned
     * and then the user is forwarded to the results page on completion.
     *
     * @param T_Url $url  URL to forward to
     * @return T_Form_Container  fluent interface
     */
    function setForward(T_Url $url)
    {
        $this->forward = $url;
        return $this;
    }

    /**
     * Get the forward URL if available.
     *
     * @return T_Url
     */
    function getForward()
    {
        return $this->forward;
    }

    /**
     * Gets the form method (get or post).
     *
     * @return string  form method
     */
    abstract function getMethod();

    /**
     * Get the form character set encoding.
     *
     * @return string  charcater set encoding
     */
    function getCharset()
    {
        return T_CHARSET;
    }

    /**
     * MIME string to encode form as.
     *
     * @return string  MIME string for form encoding
     */
    function getMimeString()
    {
        $mime = new T_Form_Mime();
        $this->accept($mime);
        return $mime->__toString();
    }

    /**
     * Sets the fieldname salt for the entire collection.
     *
     * @param string $salt  fieldname salt value
     * @param T_Filter_RepeatableHash $hash
     */
    function setFieldnameSalt($salt,T_Filter_RepeatableHash $hash)
    {
        foreach ($this->action as $a) {
            $a->setFieldnameSalt($salt,$hash);
        }
        return parent::setFieldnameSalt($salt,$hash);
    }

    /**
     * This adds a particular action option to the form.
     *
     * @param T_Form_Button $button  action to take
     * @return T_Form_Container  fluent interface
     */
    function addAction(T_Form_Button $button)
    {
        $this->action[$button->getAlias()] = $button;
        return $this;
    }

    /**
     * Gets an array of possible form button actions.
     *
     * @return array  all button actions
     */
    function getActions()
    {
        return $this->action;
    }

    /**
     * Whether any of the form components have been submitted.
     *
     * @param T_Cage_Array $source  source package to test
     * @return bool  whether any form component is submitted
     */
    function isSubmitted(T_Cage_Array $source)
    {
        if (count($this->action)>0) {
            foreach ($this->action as $button) {
        	   if ($button->isSubmitted($source)) { return true; }
            }
            return false;
        } else {
            return parent::isSubmitted($source);
        }
    }

    /**
     * Validates the entire collection.
     *
     * @param T_Cage_Array $source  source data
     * @return T_Form_Group  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        foreach ($this->action as $button) {
        	$button->validate($source);
        	if ($button->isPresent()) { break; } /* only 1 action ever present */
        }
        parent::validate($source);
        return $this;
    }

    /**
     * Whether any elements in form are present (inc actions).
     *
     * @return bool  whether any elements are present
     */
    function isPresent()
    {
        foreach ($this->action as $button) {
            if ($button->isPresent()) { return true; }
        }
        return parent::isPresent();
    }

    /**
     * Whether a specific action has been requested.
     *
     * @param string $alias  alias of the action
     * @return bool  whether this action has been requested
     */
    function isAction($alias)
    {
        if (!isset($this->action[$alias])) {
            return false;
        }
        return $this->action[$alias]->isPresent();
    }

    /**
     * Disable 'set as required'.
     *
     * While fieldsets can be set as required, semantically it does not make
     * sense if a form is set as required - this should be handled at a
     * controller level.
     *
     * @throws BadFunctionCallException
     */
    function setRequired()
    {
        throw new BadFunctionCallException();
    }

}
