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
 * T_Form_Xhtml unit tests for the T_Form_Fieldset element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlFieldsetElement extends T_Unit_Case
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
		return new T_Form_Fieldset($name,$label);
    }

	/* fieldset and legend based tests */
	
	function testThatFieldsetElementIsRenderedWithCorrectId()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//fieldset[@id="'.$element->getAlias().'"]')));
	}			

	function testRequiredXhtmlIsPresentWhenIsRequired()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//legend/span')));	
	}

	function testThatCustomAttributesAreRenderedCorrectly()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('class','testClass');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//fieldset[@class="testClass"]')));
	}	
	
	function testThatNotSetCustomAttributesAreNotRendered()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//fieldset[@class]')));		
	}

}