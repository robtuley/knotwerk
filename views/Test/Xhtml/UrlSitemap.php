<?php
/**
 * Unit test cases for the T_Xhtml_UrlSitemap class.
 *
 * @package viewTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Xhtml_UrlSitemap unit test cases.
 *
 * @package viewTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Xhtml_UrlSitemap extends T_Unit_Case
{

    /**
     * Asserts a <url>..<url> node represents the URL.
     *
     * @param T_Xml_Element $xml
     * @param T_Url_Xhtml $url
     */
    protected function assertXmlNodeIsUrl(T_Xml_Element $sxe, T_Url_Xhtml $url)
    {
        $link = (string) $sxe->loc;
        $this->assertSame($link,$url->getUrl());
        $freq = (string) $sxe->changefreq;
        $expect = (string) $url->getChangeFreq();
        $this->assertSame($freq,$expect);
        $priority = (string) $sxe->priority;
        $expect = (string) $url->getPriority();
        $this->assertSame($priority,$expect);
    }

    function testEmptyVisitorHasNoEntries()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        foreach ($sitemap->children() as $v) {
        	$this->fail();
        }
    }

    function testRootTagIsUrlset()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $this->assertSame($sitemap->getName(),'urlset');
    }

    function testAddsSingleUrl()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $url = new T_Url_Collection('root','test','p.com');
        $url->accept($sitemap);
        $this->assertXmlNodeIsUrl($sitemap->url,$url);
    }

    function testAddsParentAndChildrenUrls()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $parent = new T_Url_Collection('root','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');
        $parent->addChild($child1);
        $parent->addChild($child2);
        $parent->accept($sitemap);
        $child_sxe = array();
        foreach ($sitemap->children() as $c) {
        	$child_sxe[] = $c;
        }
        $this->assertXmlNodeIsUrl($child_sxe[0],$parent);
        $this->assertXmlNodeIsUrl($child_sxe[1],$child1);
        $this->assertXmlNodeIsUrl($child_sxe[2],$child2);
    }

    function testUrlIsEscapedBeforeBeingIncluded()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $url = new T_Url_Collection('root','test','p.com');
        $url->setParameters(array('name1'=>'value','name2'=>'value'));
            // & between parameters should be escaped.
        $url->accept($sitemap);
        $this->assertXmlNodeIsUrl($sitemap->url,$url);
            // escaped when read in, not escaped when read out ...
        $xml = $sitemap->asXml();
        $link = $url->getUrl(new T_Filter_Xhtml());
        $this->assertContains($link,$xml);
            // escaped in XML output.
    }

    function testNullChangeFreqProducesNoXmlTag()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $url = new T_Url_Collection('root','test','p.com');
        $url->accept($sitemap);
        $this->assertNotContains('<changefreq>',$sitemap->asXml());
    }

    function testNotNullChangeFreqProducesChangeFreqNode()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $url = new T_Url_Collection('root','test','p.com');
        $url->setChangeFreq('always');
        $url->accept($sitemap);
        $this->assertXmlNodeIsUrl($sitemap->url,$url);
        $this->assertContains('<changefreq>',$sitemap->asXml());
    }

    function testXmlRenderedFromToString()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $url = new T_Url_Collection('root','test','p.com');
        $url->setChangeFreq('always');
        $url->accept($sitemap);
        $this->assertSame($sitemap->__toString(),$sitemap->asXml());
    }

    function testXmlRenderedToBuffer()
    {
        $sitemap = new T_Xhtml_UrlSitemap();
        $url = new T_Url_Collection('root','test','p.com');
        $url->setChangeFreq('always');
        $url->accept($sitemap);
        ob_start();
        $test = $sitemap->toBuffer();
        $this->assertSame($sitemap,$test,'fluent interface');
        $content = ob_get_clean();
        $this->assertSame($content,$sitemap->asXml());
    }

}