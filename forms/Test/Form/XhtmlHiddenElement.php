<?php
/**
 * Unit test cases for T_Form_Xhtml class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Xhtml unit tests for the T_Form_Hidden element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlHiddenElement extends T_Unit_Case
{

    /**
     * Get XML.
     *
     * @return SimpleXMLElement
     */
    function getXmlFrom($element)
    {
    	$visitor = new T_Form_Xhtml();
    	$element->accept($visitor);
    	return new SimpleXMLElement('<fragment>'.$visitor->__toString().'</fragment>');
    }

    /**
     * Get the test element.
     *
     * @return T_Form_Text
     */
    function getElement($name,$value)
    {
    	return new T_Form_Hidden($name,$value);
    }

	function testThatInputElementIsRenderedWithCorrectType()
	{
		$element = $this->getElement('name', 'value');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(2,count($comment=$xml->xpath('//input[@type="hidden"]')));
	}

	function testThatInputElementIsRenderedWithCorrectNameWhenNotSalted()
	{
		$element = $this->getElement('name', 'value');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@name="'.$element->getFieldname().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectNameWhenIsSalted()
	{
		$element = $this->getElement('name', 'value');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@name="'.$element->getChecksumFieldname().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectValueWhenNotSalted()
	{
		$element = $this->getElement('name', 'value');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@value="'.$element->getFieldValue().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectValueWhenIsSalted()
	{
		$element = $this->getElement('name', 'value');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@value="'.$element->getChecksumFieldValue().'"]')));
	}

	function testThatCustomAttributesAreRenderedCorrectly()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('class','testClass');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@class="testClass"]')));
	}

	function testThatNotSetCustomAttributesAreNotRendered()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//input[@class]')));
	}

}
