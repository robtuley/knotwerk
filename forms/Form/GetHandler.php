<?php
/**
 * Contains the T_Form_GetHandler class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Get form intercepting filter.
 *
 * @package forms
 */
class T_Form_GetHandler extends T_Response_Filter
{

    /**
     * GET form.
     *
     * @var T_Form_Get
     */
    protected $form;

    /**
     * Environment.
     *
     * @var T_Environment
     */
    protected $env;

    /**
     * Store the form structure.
     *
     * @param T_Form_Get  $form  GET form
     * @param T_Environment  Environment
     * @param T_Response_Filter $filter  The prior filter object
     */
    function __construct(T_Form_Get $form,
                         T_Environment $env,
                         T_Response_Filter $filter=null)
    {
        parent::__construct($filter);
        $this->form = $form;
        $this->env = $env;
    }

    /**
     * Pre-filter validates the form.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPreFilter(T_Response $response)
    {
        if ($this->form->isSubmitted($get=$this->env->input('GET'))) {
            $this->form->validate($get);
        }
    }

    /**
     * Prepare filter requires no action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPrepareFilter(T_Response $response) { }

    /**
     * Post-filter not required.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPostFilter(T_Response $response) { }

    /**
     * Abort-filter requires no action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doAbortFilter(T_Response $response) { }

    /**
     * Gets the encapsulated form.
     *
     * @return T_Form_Get  encapsulated form.
     */
    function getForm()
    {
        return $this->form;
    }

}
