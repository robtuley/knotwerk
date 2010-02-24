<?php
/**
 * Contains the T_Response_ConditionalGet class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Conditional GET intercepting filter.
 *
 * This class defines an intercepting filter that implements the logic behind a
 * conditional GET request based on the last modified time of a resource. A
 * conditional GET examines the headers from the incoming request to see if the
 * client already has an up-to-date copy of the resource. If they do, then a
 * 304 Not Modified header is returned with no content, otherwise the normal
 * content is returned. It is a very useful method of reducing bandwidth for
 * RSS feeds or CSS/JS files.
 *
 * <code>
 * $page->addFilter(new T_Response_ConditionalGet($last_modified));
 * </code>
 *
 * @package controllers
 */
class T_Response_ConditionalGet extends T_Response_Filter
{

    /**
     * Response last modified time.
     *
     * @var int
     */
    protected $lm;

    /**
     * Server inputs.
     *
     * @var T_Cage_Array
     */
    protected $server;

    /**
     * Accept the last modified time of resource.
     *
     * The constructor for this class accepts The (local) time at which the
     * data that is being served was last modified.
     *
     * @param int  $last_modified  local time at which resource was last modified
     * @param T_Response_Filter $filter  The prior filter object
     */
    function __construct($last_modified,T_Environment $env,
                         T_Response_Filter $filter=null)
    {
        parent::__construct($filter);
        $this->lm = (int) $last_modified;
        $server = $env->input('SERVER');
        $this->server = ($server ? $server : new T_Cage_Array(array()));
    }

    /**
     * Pre-filter parses request to see if the client already has the most
     * up-to-date copy.
     *
     * @param T_Response $response  encapsulated response to filter
     * @throws T_Response  alternative response
     */
    protected function doPreFilter(T_Response $response)
    {
        if ($this->isCurrent()) {  // client has current copy
            $alt = new T_Response(304);
            $this->setHeader($alt);
            $response->abort();
            throw $alt;
        } else {
            $this->setHeader($response);
        }
    }

    /**
     * Adds Etag and last-modified headers to response.
     *
     * @param T_Response $response  Response to add headers to
     */
    protected function setHeader(T_Response $response)
    {
        $date = gmdate('D, d M Y H:i:s \G\M\T',$this->lm);
        $response->setHeader('Last-Modified',$date);
        $response->setHeader('Etag',$this->getEtag());
    }

    /**
     * Whether the client already has a current copy.
     *
     * @return bool whether the client copy is current
     */
    protected function isCurrent()
    {
        // if either header is missing, treat as not current.
        if (!$this->server->exists('HTTP_IF_MODIFIED_SINCE') ||
            !$this->server->exists('HTTP_IF_NONE_MATCH')     ) {
            return false;
        }
        // extract both headers
        try {
            $modified = $this->server->asScalar('HTTP_IF_MODIFIED_SINCE')
                                     ->filter(new T_Validate_UnixDate())
                                     ->uncage();
            $etag = $this->server->asScalar('HTTP_IF_NONE_MATCH')
                                 ->filter('mb_trim')
                                 ->filter('mb_strtolower')
                                 ->uncage();
        } catch (T_Exception_Filter $e) {
            // retrieval failed, deliver full content.
            return false;
        }
        // compare headers with current data
        if ($modified<$this->lm ||
            strcmp($etag,$this->getEtag())!==0) {
            return false;  // old content
        }
        return true;
    }

    /**
     * Get the Etag unique identifier
     *
     * @return string  current Etag value
     */
    protected function getEtag()
    {
        return md5($this->lm);
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
    protected function doPostFilter(T_Response $response) { }

    /**
     * Abort-filter erases the output buffer.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doAbortFilter(T_Response $response) { }

}
