<?php
class T_Test_Curl_Get extends T_Test_Curl_Harness
{

    function testCurlRequestRetrievesUrlPassedInConstructor()
    {
        $url = 'http://knotwerk.com';
        $r = new T_Curl_Get($url);
        $r->execute();
        $this->assertSame(200,$r->getCode());
        $this->assertSame(file_get_contents($url),$r->getBody());
    }

    function testCurlRequestIncludesAnyHeadersPassedIntoConstructor()
    {
        $url = 'http://knotwerk.com';
        $r = new T_Curl_Get($url,array('X-Custom'=>'SomeVar'));
        curl_setopt($r->getHandle(),CURLINFO_HEADER_OUT,true);
        curl_exec($r->getHandle());
        $test = curl_getinfo($r->getHandle(),CURLINFO_HEADER_OUT);
        $this->assertContains('X-Custom: SomeVar',$test);
    }

}