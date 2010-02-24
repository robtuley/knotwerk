<?php
class T_Test_Curl_Request extends T_Test_Curl_Harness
{

    function getCurlRequest($url=null)
    {
        if (!$url) $url = 'http://news.google.com/news?ned=us&topic=h&output=rss';
         // ^ we use a GET request to Google's news feed, as it is XML
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,2); // wait 4 s to connect
        curl_setopt($curl,CURLOPT_TIMEOUT,10); // and 10s in total
        curl_setopt($curl,CURLOPT_URL,$url);
        return new T_Curl_Request($curl);
    }

    function testCurlRequestCanBeExecutedAndXMLBodyAndHeadersAccessed()
    {
        $r = $this->getCurlRequest();

        // test body/etc is null by default
        $this->assertTrue(is_null($r->getCode()));
        $this->assertTrue(is_null($r->getBody()));
        $this->assertTrue(count($r->getHeaders())==0);

        // test handle can be accessed
        $this->assertTrue(is_resource($r->getHandle()));

        // test execute sends request
        $this->assertSame($r,$r->execute(),'fluent interface for exe');

        // check that code is populated, headers can be accessed, xml text and
        // simplexml can be created..
        $this->assertSame(200,$r->getCode());
        $this->assertTrue(count($r->getHeaders())>0);
        $this->assertTrue($r->getXml() instanceof SimpleXMLElement);
        $this->assertSame($r->getXml(),$r->getXml(),'cached xml');
        $this->assertTrue(strlen($r->getBody())>0);

        // test that once executed, request fails if tried again..
        try {
            $r->execute();
            $this->fail('Curl executes twice');
        } catch (T_Exception_Curl $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testCurlRequestCanHandleA404Request()
    {
        $r = $this->getCurlRequest('http://knotwerk.com/testing404response');
        $r->execute();

        $this->assertSame($r->getCode(),404);
        try {
            $r->getXml();
            $this->fail();
        } catch (T_Exception_Curl $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testThatCodeCanBeExplicitallySetForRequest()
    {
        $r = $this->getCurlRequest();
        $this->assertSame($r,$r->setCode(100));
        $this->assertSame($r->getCode(),100);
    }

    function testCurlRequestFailure()
    {
        $r = $this->getCurlRequest('notaurl');
        try {
            $r->execute();
            $this->fail();
        } catch (T_Exception_Curl $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }



}