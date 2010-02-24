<?php
/**
 * Unit test cases for the T_Response class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Response unit test cases.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Response extends T_Unit_Case
{

    /**
     * Response Object.
     *
     * @var T_Response
     */
    protected $response;

    /**
     * Response object classname.
     *
     * @var string
     */
    protected $classname;

    /**
     * Array of various filter test stub filter objects.
     *
     * @var array
     */
    protected $filter = array();

    /**
     * Standard test setup.
     */
    function setUp()
    {
        parent::setUp();
        $this->classname = 'T_Response';
        $this->response  = new T_Response();
        $this->filter = array();
        $this->filter[] = new T_Test_Response_FilterStub();
        $this->filter[] = new T_Test_Response_FilterStub();
    }

    function testAppendAndCloseSingleFilter()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->assertTrue($this->filter[0]->isOnlyPreFiltered());
        $this->response->closeFilters();
        $this->assertTrue($this->filter[0]->isPreAndPostFiltered());
    }

    function testAppendAndCloseSingleFilterWithExplicitKey()
    {
        $this->response->appendFilter($this->filter[0],'akey');
        $this->assertTrue($this->filter[0]->isOnlyPreFiltered());
        $this->response->closeFilters();
        $this->assertTrue($this->filter[0]->isPreAndPostFiltered());
    }

    function testAppendAndCloseMultipleFilters()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->response->appendFilter($this->filter[1]);
        $this->assertTrue($this->filter[0]->isOnlyPreFiltered());
        $this->assertTrue($this->filter[1]->isOnlyPreFiltered());
        $this->response->closeFilters();
        $this->assertTrue($this->filter[0]->isPreAndPostFiltered());
        $this->assertTrue($this->filter[1]->isPreAndPostFiltered());
    }

    function testAppendAndCloseMultipleFiltersWithExplicitKeys()
    {
        $this->response->appendFilter($this->filter[0],'akey');
        $this->response->appendFilter($this->filter[1],'bkey');
        $this->assertTrue($this->filter[0]->isOnlyPreFiltered());
        $this->assertTrue($this->filter[1]->isOnlyPreFiltered());
        $this->response->closeFilters();
        $this->assertTrue($this->filter[0]->isPreAndPostFiltered());
        $this->assertTrue($this->filter[1]->isPreAndPostFiltered());
    }

    function testAppendAndAbortSingleFilter()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->assertTrue($this->filter[0]->isOnlyPreFiltered());
        $this->response->abort();
        $this->assertTrue($this->filter[0]->isPreFilteredAndAborted());
    }

    function testAppendAndAbortMultipleFilters()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->response->appendFilter($this->filter[1]);
        $this->assertTrue($this->filter[0]->isOnlyPreFiltered());
        $this->assertTrue($this->filter[1]->isOnlyPreFiltered());
        $this->response->abort();
        $this->assertTrue($this->filter[0]->isPreFilteredAndAborted());
        $this->assertTrue($this->filter[1]->isPreFilteredAndAborted());
    }

    function testCloseFiltersInReverseOrderToAppend()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->response->appendFilter($this->filter[1]);
        $first  = $this->filter[0]->getPreFilteredAt();
        $second = $this->filter[1]->getPreFilteredAt();
        $this->assertTrue($first<$second);
        $this->response->closeFilters();
        $first  = $this->filter[0]->getPostFilteredAt();
        $second = $this->filter[1]->getPostFilteredAt();
        $this->assertTrue($first>$second);
    }

    function testAbortFiltersInReverseOrderToAppend()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->response->appendFilter($this->filter[1]);
        $first  = $this->filter[0]->getPreFilteredAt();
        $second = $this->filter[1]->getPreFilteredAt();
        $this->assertTrue($first<$second);
        $this->response->abort();
        $first  = $this->filter[0]->getAbortedAt();
        $second = $this->filter[1]->getAbortedAt();
        $this->assertTrue($first>$second);
    }

    function testAddFilterAndRetrievalExplicitKey()
    {
        $this->response->appendFilter($this->filter[0],'thekey');
        $f = $this->response->filter('thekey');
        $this->assertSame($f,$this->filter[0]);
    }

    function testFilterRetrievalWithInvalidKey()
    {
        try {
            $this->response->filter('notakey');
            $this->fail('retrieves non-existing key');
        } catch (InvalidArgumentException $expected) { }
    }

    function testAppendFilterFail()
    {
        $this->response->appendFilter($this->filter[0],'thekey');
        try {
            $this->response->appendFilter($this->filter[1],'thekey');
            $this->fail('overwrites existing filter');
        } catch (InvalidArgumentException $expected) { }
    }

    function testCanThrowClass()
    {
        try {
            throw $this->response;
            $this->fail();
        } catch (T_Response $e) {
            $this->assertSame($this->response,$e);
        }
    }

    function testisAbortedReturnFalseByDefault()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->assertFalse($this->response->isAborted());
    }

    function testisAbortedReturnTrueOnceIsAborted()
    {
        $this->response->appendFilter($this->filter[0]);
        $this->response->abort();
        $this->assertTrue($this->response->isAborted());
    }

}