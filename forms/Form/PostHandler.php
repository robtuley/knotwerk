<?php
/**
 * Contains the T_Form_PostHandler class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * POST form intercepting filter.
 *
 * This class defines an intercepting filter that deals with a POSTed form on
 * input. It is a little more complex than its GET alternative as it enforces
 * a POST-Redirect-GET scheme on valid form submission, and also helps prevent
 * CRSF attacks through the use of salting the form, locking it to a particular
 * thread value, and adding a form timeout.
 *
 * @package forms
 */
class T_Form_PostHandler extends T_Response_Filter
{

    /**
     * POST form.
     *
     * @var T_Form_Post
     */
    protected $form;

    /**
     * Enviroment.
     *
     * @var T_Environment
     */
    protected $env;

    /**
     * Hash filter.
     *
     * @var T_Filter_Repeatable
     */
    protected $hash;

    /**
     * Thread value to lock to (e.g. User ID).
     *
     * @var string
     */
    protected $lock_to = false;

    /**
     * Form timeout (seconds).
     *
     * @var int
     */
    protected $timeout;

    /**
     * Form forward (original).
     *
     * @var T_Url
     */
    protected $forward;

    /**
     * Create POST form filter.
     *
     * @param T_Form_Post  $form  form
     * @param string $lock_to  a value that form can be locked to (e.g. User ID)
     * @param int $timeout  form timeout (seconds), defaults to 15 minutes
     * @param T_Response_Filter $filter  The prior filter object
     */
    function __construct(T_Form_Post $form,T_Environment $env,
                         T_Filter_RepeatableHash $hash,
                         $lock_to=null,$timeout=900,T_Response_Filter $filter=null)
    {
        parent::__construct($filter);
        $this->env = $env;
        $this->form = $form;
        $this->hash = $hash;
        if (strlen($lock_to)>0) {
            $this->lock_to = md5($lock_to); // protect possibly sensitive user ids, etc.
        }
        $this->timeout = $timeout;
        $this->forward = $this->form->getForward();
        if (!$this->forward) {
            $msg = 'Form '.$this->form->getAlias().' has no forward set';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Pre-filter actions any submission, or prepares the form.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPreFilter(T_Response $response)
    {
        $t_field = $this->form->getAlias().'_timeout';
        $l_field = $this->form->getAlias().'_thread_lock';
        $s_field = $this->form->getAlias().'_salt';

        // prepare form:
        //   (a) add thread lock if required
        //   (b) add timeout
        $timeout = new T_Form_Hidden($t_field,$this->timeout+time());
        $this->form->addChild($timeout);
        if ($this->lock_to) {
            $lock_to = new T_Form_Hidden($l_field,$this->lock_to);
            $this->form->addChild($lock_to);
        }

        // process form if is POST:
        if ($this->env->isMethod('POST')) {
            try {
                // create salt field and validate to get salt
                $salt = new T_Form_Hidden($s_field,null);
                if ($salt->isSubmitted($this->env->input('POST'))) {
                    $salt->validate($this->env->input('POST'));
                }
                // salt form and validate
                if ($salt->isPresent() && $salt->isValid()) {
                    $salt = $salt->getValue();
                    $this->form->setFieldnameSalt($salt,$this->hash);
                    if ($this->form->isSubmitted($this->env->input('POST'))) {
                        $this->form->validate($this->env->input('POST'));
                    }
                }
                // check timeout and thread lock
                if ($this->form->isPresent() && $this->form->isValid()) {
                    // check timeout
                    $timeout = $this->form->search($t_field)->getValue();
                    if ($timeout<time()) {
                        $msg = 'This form has expired. Please submit the form '.
                               'again to complete your request.';
                        throw new T_Exception_Filter($msg);
                    }
                    // check lock thread
                    if ($this->lock_to) {
                        $lock_to = $this->form->search($l_field)->getValue();
                        if (strcmp($lock_to,$this->lock_to)!==0) {
                            $msg = 'A technical error occurred at our end, sorry. '.
                                   'Please submit the form again.';
                            throw new T_Exception_Filter($msg);
                        }
                    }
                }
            } catch (T_Exception_Filter $e) {
                $this->form->setError(new T_Form_Error($e->getMessage()));
            }
        }

        // ready form for redisplay (remember an error may be added in the POST
        // method so make even a valid form ready for display).
        //   (a) Set form forward as same page
        //   (b) Salt form and add salt hidden input
        //   (c) Reset timeout from now
        $this->form->setForward($this->env->getRequestUrl()
                                          ->setParameters(
                                    $this->env->input('GET')->uncage())
                                );
        $salt = uniqid(rand(),true);
        $this->form->setFieldnameSalt($salt,$this->hash);
        $this->form->addChild(new T_Form_Hidden($s_field,$salt));
            // note that the salt hidden input is added *after* the form is
            // salted as this input needs to be plain.
        $this->form->search($t_field)->setValue($this->timeout+time());
    }

    /**
     * Prepare-filter forwards to get if form was present and valid.
     *
     * At this point, the request has not been sent, but has been created and is about to be.
     * If the request is valid, we want to skip out *before* the form is sent and simply
     * redirect.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPrepareFilter(T_Response $response)
    {
        if ($this->form->isPresent() && $this->form->isValid() && $this->forward) {
            // POST request is successful, therefore redirect to GET so the
            // back button cannot be used to repeat the request.
            $response->abort();
            throw new T_Response_Redirect($this->forward->getUrl());
        }
    }

    /**
     * Post filter requires no action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPostFilter(T_Response $response) { }

    /**
     * Suppress PRG (Post-Redirect-Get).
     *
     * By default, a successful form submission results in a redirect to GET
     * after the form has been processed in the POST() controller method. To
     * suppress this behaviour and deliver the next page over POST on a successful
     * submission use this method.
     *
     * @return T_Form_PostHandler
     */
    function suppressPrg()
    {
        $this->forward = false;
        return $this;
    }

    /**
     * Abort-filter: no action as form might be invalid maintain form.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doAbortFilter(T_Response $response) { }

}
