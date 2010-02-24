<?php
/**
 * Contains the T_Response_Filter class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Master intercepting filter class.
 *
 * This abstract class is the parent class for all the other intercepting filter
 * classes. Like data filters, the request filter library is based on the
 * Decorator design pattern so filters can be nested, or 'piped'.
 *
 * <code>
 * $filter = new SomeFilter(new SomeOtherFilter());
 * $filter->preFilter($response);
 * </code>
 *
 * The filter functions as a pair of actions, the preFilter executed
 * before any response handling, and the postFilter after the response has been
 * sent.
 *
 * <code>
 * $filter = new SomeFilter(new SomeOtherFilter());
 * $filter->preFilter($response);
 * // .. handle request ..
 * $filter->postFilter($response);
 * </code>
 *
 * The filters can also be aborted - this is useful if the response that has
 * been built is to be discarded and a new response thrown instead.
 *
 * <code>
 * $filter = new SomeFilter(new SomeOtherFilter());
 * $filter->preFilter($response);
 * // .. handle request .. some exceptional condition ...
 * $filter->abortFilter($response);
 * </code>
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
abstract class T_Response_Filter
{

    /**
     * Prior filter object.
     *
     * This variable stores a filter object that needs to be executed before
     * the current filter object.
     *
     * @var T_Response_Filter
     */
    protected $filter;

    /**
     * Whether the filter is in progress (pre-, but not yet post-filtered).
     *
     * @var bool
     */
    protected $is_exe = false;

    /**
     * Register a prior filter.
     *
     * The constructor for the class accepts another filter object that is to
     * be executed before the current filter object. The argument is optional,
     * and a null value indicates no prior filter need be applied.
     *
     * @param T_Response_Filter $filter  The prior filter object
     */
    function __construct(T_Response_Filter $filter=null)
    {
        $this->filter = $filter;
    }

    /**
     * Executes the pre-filter action.
     *
     * This method applies the pre-filter actions to the passed response. It
     * executes any piped filters first.
     *
     * @param T_Response $response  response object to apply filter to
     */
    function preFilter(T_Response $response)
    {
        if ($this->filter) {
            $this->filter->preFilter($response);
        }
        $this->doPreFilter($response);
        $this->is_exe = true;
    }

    /**
     * Abstract container for pre-filter action.
     *
     * This abstract function is defined by specific filter objects that inherit
     * from this class. Such objects need simply to define this function, which
     * has the response object as an input.
     *
     * @param T_Response $response  encapsulated request to filter
     */
    abstract protected function doPreFilter(T_Response $response);

    /**
     * Executes the post-filter action.
     *
     * This method applies the post-filter actions to the passed response. It
     * executes any piped filters first.
     *
     * @param T_Response $response  response object to apply filter to
     */
    function postFilter(T_Response $response)
    {
        $this->doPostFilter($response);
        $this->is_exe = false;
        if ($this->filter) {
            $this->filter->postFilter($response);
        }
    }

    /**
     * Abstract container for post-filter action.
     *
     * This abstract function is defined by specific filter objects that inherit
     * from this class. Such objects need simply to define this function, which
     * has the request object as an input.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    abstract protected function doPostFilter(T_Response $response);

    /**
     * Aborts the filter action.
     *
     * This method is called in place of the post-filter when the response is
     * not to be sent, and the filters applied must be aborted.
     *
     * @param T_Response $response  response object to abort filter on
     */
    function abortFilter(T_Response $response)
    {
        if ($this->is_exe) {
            $this->doAbortFilter($response);
            $this->is_exe = false;
        }
        if ($this->filter) {
            $this->filter->abortFilter($response);
        }
    }

    /**
     * Abstract container for abort-filter action.
     *
     * @param T_Response $response  encapsulated response to abort filter
     */
    abstract protected function doAbortFilter(T_Response $response);

    /**
     * Executes the pre-send prepare hook action.
     *
     * This method applies the prepare filter method (this is a pre-send event hook) that
     * is applied before sending the encapsulated response, but after the main controller
     * method has been called.
     *
     * @param T_Response $response  response object to apply filter to
     */
    function prepareFilter(T_Response $response)
    {
        $this->doPrepareFilter($response);
        if ($this->filter) {
            $this->filter->prepareFilter($response);
        }
    }

    /**
     * Abstract container for prepare-filter action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    abstract protected function doPrepareFilter(T_Response $response);

}