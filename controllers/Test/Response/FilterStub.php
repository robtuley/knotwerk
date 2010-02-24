<?php
/**
 * Contains the T_Test_Response_FilterStub class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Intercepting Filter testing stub.
 *
 * This class is used to test the filter handling of T_Response objects, by
 * tracking the filter prefilter, postfilter and abort calls.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Response_FilterStub extends T_Response_Filter implements T_Test_Stub
{

    /**
     * Incident counter.
     *
     * This simply increments on every incident that occurs with this filter, so
     * the occurence of different events can be compared.
     *
     * @var int
     */
    private static $count = 0;

    /**
     * Count at which the filter has been pre-filtered.
     *
     * @var mixed
     */
    protected $isPreFiltered = false;

    /**
     * Count at which the filter has been post-filtered.
     *
     * @var mixed
     */
    protected $isPostFiltered = false;

    /**
     * Count at which the filter has been aborted.
     *
     * @var mixed
     */
    protected $isAborted = false;

    /**
     * Count at which the filter has been prepared.
     *
     * @var mixed
     */
    protected $isPrepared = false;

    /**
     * Pre-filter.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPreFilter(T_Response $response)
    {
        $this->isPreFiltered = self::$count++;
    }

    /**
     * Post-filter.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPostFilter(T_Response $response)
    {
        $this->isPostFiltered = self::$count++;
    }

    /**
     * Abort-filter.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doAbortFilter(T_Response $response)
    {
        $this->isAborted = self::$count++;
    }

    /**
     * Prepare-filter.
     *
     * @param T_Response $response  encapsulated response to filter
     */
    protected function doPrepareFilter(T_Response $response)
    {
        $this->isPrepared = self::$count++;
    }

    /**
     * Whether the filter is only pre-filtered.
     *
     * @return bool
     */
    function isOnlyPreFiltered()
    {
        return $this->isPreFiltered!==false &&
               !$this->isPostFiltered &&
               !$this->isAborted;
    }

    /**
     * Whether the filter is pre- and post-filtered.
     *
     * @return bool
     */
    function isPreAndPostFiltered()
    {
        return $this->isPreFiltered!==false &&
               $this->isPostFiltered!==false &&
               !$this->isAborted;
    }

    /**
     * Whether the filter is prepared.
     *
     * @return bool
     */
    function isPrepared()
    {
        return $this->isPrepared!==false;
    }

    /**
     * Whether the filter is pre-filtered then aborted.
     *
     * @return bool
     */
    function isPreFilteredAndAborted()
    {
        return $this->isPreFiltered!==false &&
               !$this->isPostFiltered &&
               $this->isAborted!==false;
    }

    /**
     * Whether the filter has been aborted.
     *
     * @return bool
     */
    function isAborted()
    {
        return $this->isAborted !== false;
    }

    /**
     * Count at which filter was pre-filtered.
     *
     * @return mixed  count at which was pre-filtered
     */
    function getPreFilteredAt()
    {
        return $this->isPreFiltered;
    }

    /**
     * Count at which filter was pre-filtered.
     *
     * @return mixed  count at which was pre-filtered
     */
    function getPreparedAt()
    {
        return $this->isPrepared;
    }

    /**
     * Count at which filter was post-filtered.
     *
     * @return mixed  count at which was post-filtered
     */
    function getPostFilteredAt()
    {
        return $this->isPostFiltered;
    }

    /**
     * Count at which filter was aborted.
     *
     * @return mixed  count at which was aborted
     */
    function getAbortedAt()
    {
        return $this->isAborted;
    }

}