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
 * T_Form_Xhtml unit tests for the T_Form_TextArea element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlTextAreaElement extends T_Unit_Case
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
    	return new T_Form_TextArea($name,$label);
    }

	// label based tests

	function testThatTextAreaElementLabelIsRenderedWithCorrectFor()
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

	// textarea based tests

	function testThatTextAreaElementIsRenderedWithCorrectNameWhenNotSalted()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//textarea[@name="'.$element->getFieldname().'"]')));
	}

	function testThatTextAreaElementIsRenderedWithCorrectNameWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//textarea[@name="'.$element->getFieldname().'"]')));
	}

	function testThatTextAreaElementIsRenderedWithCorrectIdWhenIsNotSalted()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//textarea[@id="'.$element->getAlias().'"]')));
	}

	function testThatTextAreaElementIsRenderedWithCorrectIdWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//textarea[@id="'.$element->getAlias().'"]')));
	}

	function testThatCustomAttributesAreRenderedCorrectly()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('class','testClass');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//textarea[@class="testClass"]')));
	}

	function testThatNotSetCustomAttributesAreNotRendered()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//textarea[@class]')));
	}

}
