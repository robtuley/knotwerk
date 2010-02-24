<?php
class T_Test_Curl_Post extends T_Test_Curl_Harness
{

    protected function getParams()
    {
        return array( 'js_code'=>'alert("hello");// This comment should be stripped',
                      'compilation_level'=>'WHITESPACE_ONLY',
                      'output_format'=>'text',
                      'output_info'=>'compiled_code');
    }

    protected function getUrl()
    {
        return 'http://closure-compiler.appspot.com/compile';
    }

    protected function getExpected()
    {
        return 'alert("hello");';
    }

    function testCanSendPostRequestWithParameterArray()
    {
        $params = $this->getParams();
        $request = new T_Curl_Post($this->getUrl(),$params);
        $test = trim($request->execute()->getBody());
        $this->assertSame($this->getExpected(),$test);
    }

    function testCanSendPostRequestWithParameterString()
    {
        $params = $this->getParams();
        $content = http_build_query($params,null,'&');
        $request = new T_Curl_Post($this->getUrl(),$content);
        $test = trim($request->execute()->getBody());
        $this->assertSame($this->getExpected(),$test);
    }

    function testCanSendPostRequestWithAdditionalHeaders()
    {
        $params = $this->getParams();
        $r = new T_Curl_Post($this->getUrl(),$params,array('X-Custom'=>'SomeVar'));
        curl_setopt($r->getHandle(),CURLINFO_HEADER_OUT,true);
        curl_exec($r->getHandle());
        $test = curl_getinfo($r->getHandle(),CURLINFO_HEADER_OUT);
        $this->assertContains('X-Custom: SomeVar',$test);
    }

}