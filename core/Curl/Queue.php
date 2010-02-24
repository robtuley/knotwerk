<?php
/**
 * Contains T_Curl class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates multiple CURL POST request.
 *
 * @package core
 */
class T_Curl_Queue
{

    /**
     * Master multi_curl handle.
     *
     * @var resource
     */
    protected $mc;

    /**
     * Array of queued requests.
     *
     * @var T_Curl_Request[]
     */
    protected $queue = array();

    /**
     * Array of currently executing requests.
     *
     * @var T_Curl_Request[]
     */
    protected $exe = array();

    /**
     * Array of completed requests.
     *
     * @var T_Curl_Request[]
     */
    protected $done = array();

    /**
     * Max number of simultaneous connections.
     *
     * @var int
     */
    protected $limit;

    /**
     * Create multi-CURL handler.
     *
     * @param int $limit  maximum number of simulataneous connections permitted
     */
    function __construct($limit=5)
    {
        $this->limit = $limit;
        $this->mc = curl_multi_init();
    }

    /**
     * Register a CURL request to execute.
     *
     * @param string $key  key to identify request by
     * @param T_Curl_Request  the curl request to execute
     * @return T_Curl  fluent interface
     */
    function queue(T_Curl_Request $request)
    {
        $key = (string) $request->getHandle();
        if ($this->isKey($key)) {
            $msg = "$key CURL request has already been queued";
            throw new T_Exception_Curl($msg);
        }
        $this->flush();
        if (count($this->exe)>=$this->limit) {
            // already at connection limit, queue the request
            $this->queue[$key] = $request;
        } else {
            // can start the request straight away as we are within limit
            $err = curl_multi_add_handle($this->mc,$request->getHandle());
            if ($err!==0) {
                $msg = "Failed to queue CURL request with code $err";
                throw new T_Exception_Curl($msg);
            }
            $this->exe[$key] = $request;
        }
        $this->flush();
        return $this;
    }

    /**
     * This pings a particular request in the stack to see if it is complete.
     *
     * @param T_Curl_Request $request  request to ping
     * @return bool  whether the request has completed.
     */
    function ping(T_Curl_Request $request)
    {
        $this->flush();
        $key = (string) $request->getHandle();
        return isset($this->done[$key]);
    }

    /**
     * This blocks until a particular request is complete.
     *
     * @param T_Curl_Request $request  request to wait on
     * @return T_Curl  fluent interface
     */
    function waitFor(T_Curl_Request $request)
    {
        $key = (string) $request->getHandle();
        if (!$this->isKey($key)) {
            $msg = "No queued CURL request with key $key";
            throw new T_Exception_Curl($msg);
        }
        while (!$this->ping($request)) {
            usleep(50); // pause before trying again
        }
        return $this;
    }

    /**
     * This blocks until all requests are complete.
     *
     * @return T_Curl
     */
    function waitForAll()
    {
        $i = 0;
        while (count($this->exe)>0 || count($this->queue)>0) {
            if ($i!=0) usleep(50);
            $i++;
            $this->flush();
        }
        return $this;
    }

    /**
     * Examines the executing stack and flushes completed items.
     *
     * @return void
     */
    protected function flush()
    {
        if (count($this->exe)==0) return; // nothing running
        $code = curl_multi_exec($this->mc,$running);
        if ($code==CURLM_CALL_MULTI_PERFORM) {
            // the entire queue is still running, with nothing finished.
            // in this case there is nothing to flush, so we return immediately.
            return;
        } elseif ($code==CURLM_OK) {
            // something(s) in the stack has completed, so we need to get whatever
            // has finished out of the stack into the completed array, and if
            // there are queued requests get those started..
            while ($done=curl_multi_info_read($this->mc)) {
                $hd = $done['handle'];
                $key = (string) $hd;

                // get response code, and move request into the done stack
                $info = curl_getinfo($hd);
                if (isset($info['http_code'])) $this->exe[$key]->setCode($info['http_code']);
                $this->done[$key] = $this->exe[$key];
                unset($this->exe[$key]);

                // before removing the old request, see if there are any more
                // requests to queue up, and if so set them going.
                if (count($this->queue)>0) {
                    $r = array_shift($this->queue);
                    $k = (string) $r->getHandle();
                    $err = curl_multi_add_handle($this->mc,$r->getHandle());
                    if ($err!==0) {
                        $msg = "Failed to queue CURL request with code $err";
                        throw new T_Exception_Curl($msg);
                    }
                    $this->exe[$k] = $r; // move into executing array
                }

                // remove the curl handle that just completed
                curl_multi_remove_handle($this->mc,$hd);
            }
        } else {
            // something has gone wrong in the stack..
            $msg = "CURL stack has failed with code $code";
            throw new T_Exception_Curl($msg);
        }
    }

    /**
     * Whether a key exists.
     *
     * @param string $key
     * @return bool
     */
    protected function isKey($key)
    {
        return isset($this->exe[$key]) ||
               isset($this->queue[$key]) ||
               isset($this->done[$key]);
    }


}