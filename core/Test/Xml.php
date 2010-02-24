<?php
/**
 * Unit test cases for the T_Xml class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Xml unit test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Xml extends T_Unit_Case
{

    /**
     * Asserts that two values are equal by serializing them.
     *
     * This is useful as the SimpleXML object is complex and can hold state
     * information from its parents in some systems.
     *
     * @param mixed $expect
     * @param mixed $test
     * @param string $msg
     */
    function assertEqualsBySerialize($expect,$test,$msg=null)
    {
        $this->assertSame(serialize($expect),serialize($test),$msg);
    }

    function testAccessChildNode()
    {
        $xml = new T_Xml('<root><child>data</child></root>');
        $expect = new T_Xml_Element('<child>data</child>');
        $this->assertEqualsBySerialize($xml->child,$expect);
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
        	$xml_attr[$k] = $v;
        }
        foreach ($expect->attributes() as $k => $v) {
        	$expect_attr[$k] = $v;
        }
        $this->assertEqualsBySerialize($xml_attr,$expect_attr);
    }

    function testCanAccessChildrenOfXmlObject()
    {
        $data = '<root><child>One</child><child>Two</child></root>';
        $xml = new T_Xml($data);
        $expect = new T_Xml_Element($data);
        foreach ($xml->children() as $v) {
        	$xml_child[] = $v;
        }
        foreach ($expect->children() as $v) {
        	$expect_child[] = $v;
        }
        $this->assertEqualsBySerialize($xml_child,$expect_child);
    }

    function testCanAddAChildToXmlObject()
    {
        $xml = new T_Xml('<root>data</root>');
        $xml_child = $xml->addChild('child','value');
        $expect = new T_Xml_Element('<root>data</root>');
        $expect_child = $expect->addChild('child','value');
        $this->assertSame($xml->asXml(),$expect->asXml());
        $this->assertEqualsBySerialize($xml_child,$expect_child);
    }

    function testCanGetNameOfRootObject()
    {
        $xml = new T_Xml('<root>data</root>');
        $expect = new T_Xml_Element('<root>data</root>');
        $this->assertSame($xml->getName(),$expect->getName());
    }

}