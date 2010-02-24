<?php
/**
 * Contains the T_Response_Compression class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Response Compression intercepting filter.
 *
 * This class defines an intercepting filter that compresses the output whenever
 * possible. The filter can be used to dynamically set the compression level, or
 * force the output of compressed content.
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Response_Compression extends T_Response_Filter
{

    /**
     * Whether to force compression.
     *
     * @var bool
     */
    protected $force_gzip = false;

    /**
     * The level of compression.
     *
     * @var int
     */
    protected $level = 6;

    /**
     * Server inputs.
     *
     * @var T_Cage_Array
     */
    protected $server;

    /**
     * The response that this filter is attached to.
     *
     * This reference forms a circular reference, but this cannot be avoided
     * if we want to be able to force gzip compression from this class.
     *
     * @var T_Response
     */
    protected $response;

    /**
     * Create reponse filter.
     *
     * @param T_Environment $env
     * @param T_Response_Filter $filter
     */
    function __construct(T_Environment $env,T_Response_Filter $filter=null)
    {
        parent::__construct($filter);
        $server = $env->input('SERVER');
        $this->server = ($server ? $server : new T_Cage_Array(array()));
    }

    /**
     * Whether zlib compression is enabled in php.ini.
     *
     * @return bool  is Zlib compression enabled
     */
    protected function isZlibCompressed()
    {
        return (bool) ini_get('zlib.output_compression');
    }

    /**
     * Whether the client accepts compressed content.
     *
     * @return bool  if client accepts compressed content.
     */
    protected function clientAcceptsCompression()
    {
        if ($this->server->exists('HTTP_ACCEPT_ENCODING')) {
            $accept = $this->server->asScalar('HTTP_ACCEPT_ENCODING')->uncage();
            return strpos($accept,'gzip') !== false;
        }
        return false;
    }

    /**
     * Force the output to be g-zipped.
     *
     * This function forces the output to be g-zipped, even if the headers
     * provided do not explicitally include the accept-encoding header.
     */
    function forceGzipOutput()
    {
        $this->force_gzip = true;
        /* If the content is NOT normally going to be compressed, by the
           zlib library or this filter, we need to add the pre-filter header */
        if (!$this->clientAcceptsCompression()) {
            $this->response->setHeader('Content-Encoding','gzip');
            /* If the content was going to be handled by the zlib-compression
               library, we didn't start buffering, so we need to start this
               now - as we are now going to compress anyway. */
            if ($this->isZlibCompressed()) {
                ob_start();
            }
        }

        return $this;
    }

    /**
     * Set compression level (0->9).
     *
     * @param int $level  desired compression level
     */
    function setCompressionLevel($level)
    {
        if ($this->isZlibCompressed()) {
            ini_set('zlib.output_compression_level',$level);
        }
        $this->level = $level;
        return $this;
    }

    /**
     * Pre-filter starts buffer.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPreFilter(T_Response $response)
    {
        $this->response = $response;
        if ($this->isZlibCompressed()) {
            return;
        }
        ob_start();
        if ($this->clientAcceptsCompression()) {
            /* Headers must be sent in the pre-filter.
               (post filter is executed after headers sent) */
            $response->setHeader('Content-Encoding','gzip');
        }
    }

    /**
     * Prepare filter requires no action.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPrepareFilter(T_Response $response) { }

    /**
     * Post-filter compresses if possible.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPostFilter(T_Response $response)
    {
        /* A number of different situtaion can occur here:
            1. zlib on, no gzip force --> no action.
            2. zlib on, gzip force --> compress if not going to be.
            3. zlib off --> compress if clients accepts or force. */
        if ($this->isZlibCompressed() && !$this->force_gzip) {
            return; // compressed anyway if accepted.
        } elseif ($this->isZlibCompressed()) {
            if (!$this->clientAcceptsCompression()) {
                echo gzencode(ob_get_clean(),$this->level,FORCE_GZIP);
            } else {
                return; // no action, will be compressed anyway.
            }
        } elseif ($this->clientAcceptsCompression() || $this->force_gzip) {
            echo gzencode(ob_get_clean(),$this->level,FORCE_GZIP);
        } else {
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
        if (!$this->isZlibCompressed()) {
            ob_end_clean();
        }
    }

}
