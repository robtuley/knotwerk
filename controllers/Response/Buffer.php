<?php
/**
 * Contains the T_Response_Buffer class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Output buffer intercepting filter.
 *
 * This class defines an output buffer intercepting filter with the specified
 * callback function. For example, to define output buffering with a gzip
 * compression output handler:
 *
 * <code>
 * $page->addFilter(new T_Response_Buffer('ob_gzhandler'));
 * </code>
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Response_Buffer extends T_Response_Filter
{

    /**
     * Output buffer handler.
     *
     * @var string
     */
    protected $callback;

    /**
     * Accept a callback parameter.
     *
     * The constructor for this class accepts an optional function callback
     * parameter as well as an optional piped filter object.
     *
     * @param string  $callback  output buffer handler
     * @param T_Response_Filter $filter  The prior filter object
     */
    function __construct($callback=null, T_Response_Filter $filter=null)
    {
        parent::__construct($filter);
        $this->callback = $callback;
    }

    /**
     * Pre-filter starts output buffering.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPreFilter(T_Response $response)
    {
        $ok = ob_start($this->callback);
        if ($ok === false) {
            throw new InvalidArgumentException('invalid callback function.');
        }
    }

    /**
     * Prepare filter requires no action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPrepareFilter(T_Response $response) { }

    /**
     * Post-filter flushes the output buffer.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPostFilter(T_Response $response)
    {
        if (ob_get_level()>0) {
            ob_end_flush();
        }
    }

    /**
     * Abort-filter erases the output buffer.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doAbortFilter(T_Response $response)
    {
        if (ob_get_level()>0) {
            ob_end_clean();
        }
    }

}