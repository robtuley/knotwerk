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
 * T_Form_Xhtml unit tests for the T_Form_Text element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlTextElement extends T_Unit_Case
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
    function getElement($name,$label)
    {
    	return new T_Form_Text($name,$label);
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
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@type="text"]')));
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

	function testThatInputElementIsRenderedWithCorrectIdWhenIdIsOverridden()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('id','testId');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//input[@id="'.$element->getAlias().'"]')));
	}


	function testThatInputElementIsRenderedWithCorrectIdWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@id="'.$element->getAlias().'"]')));
	}

	function testThatInputElementIsRenderedWithCorrectMaxLength()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('maxlength',5);
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@maxlength="5"]')));
	}

	function testThatNullMaxLengthMeansMaxLengthAttrNotRendered()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//input[@maxlength]')));
	}

	function testThatInputElementIsRenderedWithCorrectDefault()
	{
		$element = $this->getElement('name', 'label');
		$element->setDefault('hello');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@value="'.$element->getDefault().'"]')));
	}

	function testThatInputElementCanBeRenderedWithNullDefault()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@value=""]')));
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
