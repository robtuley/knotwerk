<?php
/**
 * Unit test cases for the T_Response_ClientCache class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Response_ClientCache unit test cases.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Response_ClientCache extends T_Unit_Case
{

    /**
     * Test that no-cache has expires header in the past.
     */
    function testNoCacheExpiresInThePast()
    {
        $now = time();
        $filter = new T_Response_ClientCache(0);
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $as_time = new T_Validate_UnixDate();
        $this->assertTrue($now > $as_time->transform($headers['Expires']));
    }

    /**
     * Test that 'no-cache' included in Cache-Control & Pragma headers.
     */
    function testNoCacheSetInCacheControlAndPragmaHeaders()
    {
        $filter = new T_Response_ClientCache(0);
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $this->assertTrue(strpos($headers['Cache-Control'],'no-cache')!==false);
        $this->assertTrue(strpos($headers['Pragma'],'no-cache')!==false);
    }

    /**
     * Test expiry time is in future
     */
    function testCacheExpiresAfterSpecifiedDuration()
    {
        $filter = new T_Response_ClientCache(60*60);
        $response = new T_Test_ResponseStub();
        $now = time();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $as_time = new T_Validate_UnixDate();
        $expires = $as_time->transform($headers['Expires']);
        $this->assertTrue( abs($expires-$now-60*60) <= 2 );
           // (allow 2 second out to allow for code execution)
    }

    /**
     * Test max-age is specified in Cache-Control header.
     */
    function testCacheMaxAgeSpecifiedInCacheControlHeader()
    {
        $filter = new T_Response_ClientCache(60*60);
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $needle = 'max-age=3600';
        $this->assertTrue(strpos($headers['Cache-Control'],$needle)!==false);
    }

    /**
     * Test cache is public by default.
     */
    function testCacheIsPublicByDefault()
    {
        $filter = new T_Response_ClientCache(60*60);
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $this->assertTrue(strpos($headers['Cache-Control'],'public')!==false);
        $this->assertTrue(strpos($headers['Pragma'],'public')!==false);
    }

    /**
     * Test cache can be explicitally set to be public.
     */
    function testCacheCanBeExplicitallySetAsPublic()
    {
        $filter = new T_Response_ClientCache(60*60,'pUbLic');
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $this->assertTrue(strpos($headers['Cache-Control'],'public')!==false);
        $this->assertTrue(strpos($headers['Pragma'],'public')!==false);
    }

    /**
     * Test cache can be set as private.
     */
    function testCacheCanBePrivate()
    {
        $filter = new T_Response_ClientCache(60*60,'PrIvATe');
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $headers = $response->getHeaders();
        $this->assertTrue(strpos($headers['Cache-Control'],'private')!==false);
        $this->assertTrue(strpos($headers['Pragma'],'private')!==false);
    }

    /**
     * Test invalid type argument causes failure
     */
    function testInvalidTypeArgCausesExceptionFailure()
    {
        try {
            $filter = new T_Response_ClientCache(60*60,'notatype');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    /**
     * Test can pipe prior filter.
     */
    function testCanPipePriorFilter()
    {
        $prior = new T_Test_Response_FilterStub();
        $filter = new T_Response_ClientCache(60*60,'public',$prior);
        $response = new T_Test_ResponseStub();
        $filter->preFilter($response);
        $this->assertTrue($prior->isOnlyPreFiltered());
        $filter->postFilter($response);
        $this->assertTrue($prior->isPreAndPostFiltered());
    }

}