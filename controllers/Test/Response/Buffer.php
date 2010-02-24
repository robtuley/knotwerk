<?php
/**
 * Unit test cases for the T_Response_Buffer class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Response_Buffer unit test cases.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Response_Buffer extends T_Unit_Case
{

    /**
     * Defines test buffer handler.
     */
    function setUpSuite()
    {
        if (!function_exists('test_buffer_handler')) {
            function test_buffer_handler($data) { return $data; }
        }
    }

    /**
     * Assert buffer handlers.
     *
     * This method asserts that the code is currently nested to a certain buffer
     * level with the handlers as defined by the input array.
     *
     * @param array $handlers  buffer handlers
     */
    protected function assertBufferHandler(array $handlers)
    {
        $level = count($handlers);
        $this->assertSame(ob_get_level(),$level);
        $this->assertSame(ob_list_handlers(),$handlers);
    }

    function testFilterBufferingLevels()
    {
        $response = new T_Response();
        $filter = new T_Response_Buffer();
        $this->assertBufferHandler(array());
        $filter->preFilter($response);
        $this->assertBufferHandler(array('default output handler'));
        $filter->postFilter($response);
        $this->assertBufferHandler(array());
    }

    function testFilterFlushesContentsOfBuffer()
    {
        $response = new T_Response();
        $filter = new T_Response_Buffer();
        ob_start();
        $filter->preFilter($response);
        echo 'some-output';
        $filter->postFilter($response);
        $output = ob_get_clean();
        $this->assertSame($output,'some-output');
    }

    function testFilterWithCallback()
    {
        $response = new T_Response();
        $filter = new T_Response_Buffer('test_buffer_handler');
        $this->assertBufferHandler(array());
        $filter->preFilter($response);
        $this->assertBufferHandler(array('test_buffer_handler'));
        $filter->postFilter($response);
        $this->assertBufferHandler(array());
    }

    function testPipedFilterBufferingLevels()
    {
        $response = new T_Response();
        $pipe     = new T_Response_Buffer('test_buffer_handler');
        $filter   = new T_Response_Buffer(null,$pipe);
        $this->assertBufferHandler(array());
        $filter->preFilter($response);
        $this->assertBufferHandler(array('test_buffer_handler',
                                         'default output handler')  );
        $filter->postFilter($response);
        $this->assertBufferHandler(array());
    }

    function testFilterCallbackFailure()
    {
        $response = new T_Response();
        $filter   = new T_Response_Buffer('notahandler');
        try {
            $filter->preFilter($response);
            $this->fail('callback failure not detected');
        } catch (InvalidArgumentException $expected) {}
    }

    function testFilterAbortBufferingLevels()
    {
        $response = new T_Response();
        $filter = new T_Response_Buffer();
        $this->assertBufferHandler(array());
        $filter->preFilter($response);
        $this->assertBufferHandler(array('default output handler'));
        $filter->abortFilter($response);
        $this->assertBufferHandler(array());
    }

    function testFilterAbortDiscardsContentsOfBuffer()
    {
        $response = new T_Response();
        $filter = new T_Response_Buffer();
        ob_start();
        $filter->preFilter($response);
        echo 'some-output';
        $filter->abortFilter($response);
        $output = ob_get_clean();
        $this->assertTrue(strlen($output)==0);
    }

    function testPipedFilterAbortBufferingLevels()
    {
        $response = new T_Response();
        $pipe     = new T_Response_Buffer('test_buffer_handler');
        $filter   = new T_Response_Buffer(null,$pipe);
        $this->assertBufferHandler(array());
        $filter->preFilter($response);
        $this->assertBufferHandler(array('test_buffer_handler',
                                         'default output handler')  );
        $filter->abortFilter($response);
        $this->assertBufferHandler(array());
    }

}