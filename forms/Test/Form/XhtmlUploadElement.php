<?php
/**
 * Unit test cases for T_Form_Xhtml class.
 *
 * @package formTests
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Xhtml unit tests for the T_Form_Upload element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlUploadElement extends T_Unit_Case
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
     * @return T_Form_Upload
     */
    function getElement($name,$label)
    {
    	return new T_Form_Upload($name,$label);
    }

	// label based tests

	function testThatInputElementLabelIsRenderedWithCorrectFor()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//label[@for="'.$element->getAlias().'"]')));
	}

	function testRequiredXhtmlIsNotPresentWhenIsOptional()
	{
		$element = $this->getElement('name', 'label');
		$element->setOptional();
		$xml = $this->getXmlFrom($element);
		$this->assertNotEquals(1,count($comment=$xml->xpath('//label/em')));
	}

	function testRequiredXhtmlIsPresentWhenIsRequired()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//label/em')));
	}

	// input based tests

	function testThatInputElementIsRenderedWithCorrectType()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@type="file"]')));
	}

	function testThatInputElementIsRenderedWithCorrectNameWhenNotSalted()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@name="'.$element->getFieldname().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectNameWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@name="'.$element->getFieldname().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectIdWhenIsNotSalted()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@id="'.$element->getAlias().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectIdWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@id="'.$element->getAlias().'"]')));
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
		$this->assertNotEquals(1,count($comment=$xml->xpath('//input[@class]')));
	}
}
