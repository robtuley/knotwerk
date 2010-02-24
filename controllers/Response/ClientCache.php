<?php
/**
 * Contains the T_Response_ClientCache class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Client and proxy caching control.
 *
 * This intercepting filter controls the client and proxy caching of the
 * response by issuing a number of response headers.
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Response_ClientCache extends T_Response_Filter
{

    /**
     * Duration for which page can be cached (seconds).
     *
     * @var int
     */
    protected $cache_time;

    /**
     * The cache type (public or private).
     *
     * @var string
     */
    protected $type;

    /**
     * Accept the length content can be cached, and public/private type.
     *
     * @param int $cache_time  length to cache for (s), 0 for no cache
     * @param int $type  type of caching to apply ('public' or 'private')
     * @param T_Response_Filter $filter  The prior filter object
     */
    function __construct($cache_time,
                         $type='public',
                         T_Response_Filter $filter=null)
    {
        parent::__construct($filter);
        $this->cache_time = (int) $cache_time;
        if (strcasecmp($type,'public')!==0 && strcasecmp($type,'private')!==0) {
            throw new InvalidArgumentException('Illegal cache type '.$type);
        }
        $this->type = strtolower($type);
    }

    /**
     * Pre-filter issues cache headers.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPreFilter(T_Response $response)
    {
        if ($this->cache_time<=0) {
            $response->setHeader('Cache-Control','no-cache, must-revalidate');
              // HTTP/1.1 uses cache control
            $response->setHeader('Pragma','no-cache');
              // HTTP/1.0 uses pragma
            $expiry = time()-60*60*24*30; // expired 30 days ago.
        } else {
            $state  = "{$this->type}, max-age={$this->cache_time}, must-revalidate";
            $response->setHeader('Cache-Control',$state);
            $response->setHeader('Pragma',$this->type);
            $expiry = time()+$this->cache_time;
        }
        $date = gmdate('D, d M Y H:i:s \G\M\T',$expiry);
        $response->setHeader('Expires',$date);
    }

    /**
     * Prepare filter requires no action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPrepareFilter(T_Response $response) { }

    /**
     * Post-filter does nothing.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPostFilter(T_Response $response) { }

    /**
     * Abort-filter does nothing (headers never got sent).
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doAbortFilter(T_Response $response) { }

}
