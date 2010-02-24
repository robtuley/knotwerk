<?php
/**
 * Defines T_Compile_Closure interface.
 *
 * @package client
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An interface for javascript compilers.
 *
 * @package client
 */
class T_Compile_Closure implements T_Compile_Js
{

    /**
     * Curl Queue.
     *
     * @var T_Curl_Queue
     */
    protected $curl;

    /**
     * Curl Request.
     *
     * @var T_Curl_Post
     */
    protected $request = array();

    /**
     * Create Closure compiler.
     *
     * @param T_Curl_Queue $curl  CURL queue
     */
    function __construct(T_Curl_Queue $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Registers the filter to compile an array of files.
     *
     * @param T_Code_Group  group of files to compile
     * @return T_Compile  fluent interface
     */
    function compile(T_Code_Files $files)
    {
        // get source code
        $src = '';
        foreach ($files->getPaths() as $p)
        {
            $src .= file_get_contents($p);
        }

        // issue non-blocking request to google closure API to parse and optimize
        // the code
        $level = $files->isComplete() ? 'ADVANCED_OPTIMIZATIONS' : 'SIMPLE_OPTIMIZATIONS';
        $url = 'http://closure-compiler.appspot.com/compile';
        $params = array( 'output_format' => 'text',
                         'compilation_level' => $level,
                         'js_code' => $src,
                         'output_info' => 'compiled_code');
        $request = new T_Curl_Post($url,$params);
        $this->curl->queue($request);
        $this->request[] = $request;
        return $this;
    }

    /**
     * Gets the compiled source code (blocks to wait for compilation to finish).
     *
     * @return string
     */
    function getSrc()
    {
        // wait for CURL requests to complete
        foreach ($this->request as $r) $this->curl->waitFor($r);

        // parse XML responses and output code
        $src = null;
        foreach ($this->request as $r) {
            $src .= $r->getBody();
        }
        return $src;
    }

}