<?php
class T_Test_Xml extends T_Unit_Case
{

    function testAccessChildNode()
    {
        $xml = new T_Xml('<root><child>data</child></root>');
        $this->assertEquals($xml->child->asXml(),'<child>data</child>');
    }

    function testCanAddAnAttributeToXmlObject()
    {
        $xml = new T_Xml('<root>data</root>');
        $xml->addAttribute('attr','value');
        $expect = new T_Xml_Element('<root>data</root>');
        $expect->addAttribute('attr','value');
        $this->assertSame($xml->asXml(),$expect->asXml());
    }

    function testCanAccessAttributesOfXmlObject()
    {
        $xml = new T_Xml('<root attr="value"></root>');
        $expect = new T_Xml_Element('<root attr="value"></root>');
        foreach ($xml->attributes() as $k => $v) {
        	$xml_attr[$k] = (string) $v;
        }
        foreach ($expect->attributes() as $k => $v) {
        	$expect_attr[$k] = (string) $v;
        }
        $this->assertEquals($xml_attr,$expect_attr);
    }

    function testCanAccessChildrenOfXmlObject()
    {
        $data = '<root><child>One</child><child>Two</child></root>';
        $xml = new T_Xml($data);
        $expect = new T_Xml_Element($data);
        foreach ($xml->children() as $v) {
        	$xml_child[] = (string) $v;
        }
        foreach ($expect->children() as $v) {
        	$expect_child[] = (string) $v;
        }
        $this->assertEquals($xml_child,$expect_child);
    }

    function testCanAddAChildToXmlObject()
    {
        $xml = new T_Xml('<root>data</root>');
        $xml_child = $xml->addChild('child','value');
        $expect = new T_Xml_Element('<root>data</root>');
        $expect_child = $expect->addChild('child','value');
        $this->assertSame($xml->asXml(),$expect->asXml());
        $this->assertEquals($xml_child->asXml(),$expect_child->asXml());
    }

    function testCanGetNameOfRootObject()
    {
        $xml = new T_Xml('<root>data</root>');
        $expect = new T_Xml_Element('<root>data</root>');
        $this->assertSame($xml->getName(),$expect->getName());
    }

}