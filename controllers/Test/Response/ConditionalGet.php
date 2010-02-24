<?php
class T_Test_Response_ConditionalGet extends T_Unit_Case
{

    function getEnvironment($server=null)
    {
        $input = array();
        if ($server) $input['SERVER'] = new T_Cage_Array($server);
        return new T_Test_EnvironmentStub($input);
    }

    protected function getDateString($timestamp)
    {
        return gmdate('D, d M Y H:i:s \G\M\T',$timestamp);
    }

    protected function getEtag($timestamp)
    {
        return md5($timestamp);
    }

    // tests

    function testHeadersAreAddedToResponse()
    {
        $lm = 1188994102;
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment());
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $this->assertTrue(isset($headers['Etag']));
        $this->assertTrue(isset($headers['Last-Modified']));
    }

    function testLastModifiedHeaderContainsCorrectDate()
    {
        $lm = 1188994102;
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment());
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $as_unix = new T_Validate_UnixDate();
        $header_lm = $as_unix->transform($headers['Last-Modified']);
        $this->assertSame($header_lm,$lm);
    }

    function testEtagHeadersAreUniqueToLastModifiedTime()
    {
        $filter1 = new T_Response_ConditionalGet(1188994102,$this->getEnvironment());
        $filter2 = new T_Response_ConditionalGet(1188994110,$this->getEnvironment());
        $response1 = new T_Test_ResponseStub();
        $response2 = new T_Test_ResponseStub();
        $filter1->preFilter($response1);
        $filter2->preFilter($response2);
        $tmp = $response1->getHeaders();
        $etag1 = $tmp['Etag'];
        $tmp = $response2->getHeaders();
        $etag2 = $tmp['Etag'];
        $this->assertNotEquals($etag1,$etag2);
    }

    function testNoRequestHeadersPresentNoEffect()
    {
        $lm = 1188994102;
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment());
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
    }

    function testOldContentRequestHeadersPresentNoEffect()
    {
        $lm = 1188994102;
        $server['HTTP_IF_MODIFIED_SINCE'] = $this->getDateString($lm-10);
        $server['HTTP_IF_NONE_MATCH'] = $this->getEtag($lm-10);
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment($server));
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
    }

    function testIfModifiedSinceHeaderOnlyNoEffect()
    {
        $lm = 1188994102;
        $server['HTTP_IF_MODIFIED_SINCE'] = $this->getDateString($lm);
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment($server));
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
    }

    function testIfNoneMatchHeaderOnlyNoEffect()
    {
        $lm = 1188994102;
        $server['HTTP_IF_NONE_MATCH'] = $this->getEtag($lm);
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment($server));
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
    }

    function testInvalidIfModifiedSinceHeaderNoEffect()
    {
        $lm = 1188994102;
        $server['HTTP_IF_MODIFIED_SINCE'] = 'notAdate';
        $server['HTTP_IF_NONE_MATCH'] = $this->getEtag($lm);
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment($server));
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
    }

    function testInvalidEtagHeaderNoEffect()
    {
        $lm = 1188994102;
        $server['HTTP_IF_MODIFIED_SINCE'] = $this->getDateString($lm);
        $server['HTTP_IF_NONE_MATCH'] = 'notCorrectEtag';
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment($server));
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $filter->postFilter($response);
    }

    function testTest304ResponseWhenClientContentIsCurrent()
    {
        $lm = 1188994102;
        $server['HTTP_IF_MODIFIED_SINCE'] = $this->getDateString($lm);
        $server['HTTP_IF_NONE_MATCH'] = $this->getEtag($lm);
        $filter = new T_Response_ConditionalGet($lm,$this->getEnvironment($server));
        $response = new T_Test_ResponseStub();
        try {
            $filter->preFilter($response);
        } catch (T_Response $alt) {
            // check new response, status 304
            $this->assertNotSame($response,$alt);
            $this->assertSame(304,$alt->getStatus());
            // check that original response has been aborted
            $this->assertTrue($response->isAborted());
        }
    }

    function testCanPipePriorFilter()
    {
        $prior = new T_Test_Response_FilterStub();
        $filter = new T_Response_ConditionalGet(1188994102,
                        $this->getEnvironment(),$prior);
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $this->assertTrue($prior->isOnlyPreFiltered());
        $filter->postFilter($response);
        $this->assertTrue($prior->isPreAndPostFiltered());
    }

}
