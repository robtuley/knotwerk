<?php
/**
 * Unit test cases for the T_Response_Filter class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Response_Filter unit test cases.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Response_Filter extends T_Unit_Case
{

    function testFilterCanBePreFiltered()
    {
        $response = new T_Response();
        $filter = new T_Test_Response_FilterStub();
        $filter->preFilter($response);
        $this->assertTrue($filter->isOnlyPreFiltered());
    }

    function testPipedFilterIsAlsoPreFiltered()
    {
        $response = new T_Response();
        $filter1 = new T_Test_Response_FilterStub();
        $filter2 = new T_Test_Response_FilterStub($filter1);
        $filter2->preFilter($response);
        $this->assertTrue($filter1->isOnlyPreFiltered());
        $this->assertTrue($filter2->isOnlyPreFiltered());
    }

    function testFilterCanBePostFiltered()
    {
        $response = new T_Response();
        $filter = new T_Test_Response_FilterStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
        $this->assertTrue($filter->isPreAndPostFiltered());
    }

    function testPipedFilterIsAlsoPostFiltered()
    {
        $response = new T_Response();
        $filter1 = new T_Test_Response_FilterStub();
        $filter2 = new T_Test_Response_FilterStub($filter1);
        $filter2->preFilter($response);
        $filter2->postFilter($response);
        $this->assertTrue($filter1->isPreAndPostFiltered());
        $this->assertTrue($filter2->isPreAndPostFiltered());
    }

    function testFilterCanBeAborted()
    {
        $response = new T_Response();
        $filter = new T_Test_Response_FilterStub();
        $filter->preFilter($response);
        $filter->abortFilter($response);
        $this->assertTrue($filter->isPreFilteredAndAborted());
    }

    function testPipedFilterIsAlsoAborted()
    {
        $response = new T_Response();
        $filter1 = new T_Test_Response_FilterStub();
        $filter2 = new T_Test_Response_FilterStub($filter1);
        $filter2->preFilter($response);
        $filter2->abortFilter($response);
        $this->assertTrue($filter1->isPreFilteredAndAborted());
        $this->assertTrue($filter2->isPreFilteredAndAborted());
    }

    function testFilterIsNotAbortedIfPreFilterHasNotYetBeenExecuted()
    {
        $response = new T_Response();
        $filter = new T_Test_Response_FilterStub();
        $filter->abortFilter($response);
        $this->assertFalse($filter->isAborted());
    }

    function testFilterIsNotAbortedIfPreAndPostFilterHaveAlreadyExecuted()
    {
        $response = new T_Response();
        $filter = new T_Test_Response_FilterStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
        $filter->abortFilter($response);
        $this->assertFalse($filter->isAborted());
    }

}