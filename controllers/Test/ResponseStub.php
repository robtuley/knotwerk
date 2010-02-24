<?php
/**
 * Contains the T_Test_ResponseStub class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * HTTP Response Test Stub.
 *
 * This class extends the HTTP Response class for testing, exposing more of the
 * internal properties than are normally available.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_ResponseStub extends T_Response implements T_Test_Stub
{

    /**
     * Array of the headers sent.
     *
     * @var array
     */
    protected $headers_sent = array();

    /**
     * Whether the response has been aborted.
     *
     * @var bool
     */
    protected $is_aborted = false;

    /**
     * Gets the content type.
     *
     * @return string  content type
     */
    function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Gets the response encoding.
     *
     * @return string  response encoding
     */
    function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Get headers.
     *
     * @return array  headers to be sent
     */
    function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gets the protocol.
     *
     * @return string  protocol type
     */
    function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Don't send headers, but cache them instead.
     */
    protected function sendHeader($header)
    {
        $this->headers_sent[] = $header;
    }

    /**
     * Get an array of the headers sent.
     *
     * @return array
     */
    function getHeadersSent()
    {
        return $this->headers_sent;
    }

    /**
     * Add aborted flag to abort method
     */
    function abort()
    {
        $this->is_aborted = true;
        parent::abort();
    }

    /**
     * Whether the response has been aborted.
     *
     * @return bool  wether the response has been aborted.
     */
    function isAborted()
    {
        return $this->is_aborted;
    }

}