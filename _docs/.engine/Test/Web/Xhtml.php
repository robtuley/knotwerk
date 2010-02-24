<?php
class Test_Web_Xhtml extends T_Unit_Spider
{
    function setUpSuite()
    {
        $url = $this->getFactory()->getWebUrl();
        if (!$url) $this->skipAll('No root web page defined in unit config');
        $this->setBaseUrl($url);
    }
    function isTestableUrl($url)
    {
        $testable = parent::isTestableUrl($url);
        // skip any file with an extension, that is probably not html page
        $path = _end(explode('/',$url));
        if (strpos($path,'.')!==false) $testable = false;
        return $testable;
    }
    /*
    function load($url,$method='GET',$data=null,$headers=array())
    {
        echo $url.EOL;
        parent::load($url);
    }
    */

    // tests

    function testStatusIs200()
    {
        $this->assertStatus(200);
    }
    function testHasTitleElement()
    {
        $this->assertIsElement('/html/head/title');
    }
    function testPageIsServeredAsUTF8TextHtml()
    {
        $header = $this->getHeader('Content-Type');
        $this->assertContains('text/html',$header);
        $this->assertContains('charset=UTF-8',$header);
    }
    function testPageHasASingleH1Title()
    {
        $this->assertEquals(1,count($this->getElement('//h1')));
    }
    function testPageIsValidHtml()
    {
        $this->assertValidHtml();
    }
}
