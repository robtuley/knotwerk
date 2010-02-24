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
 * T_Form_Xhtml unit tests for the T_Form_Select element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlSelectElement extends T_Unit_Case
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
    	$element = new T_Form_Select($name,$label);
    	$element->setOptions(array('test'=>'1','dave'=>'fred', 'swadg' => 'asdgf'));
		return $element;
    }

	// label based tests

	function testThatSelectElementLabelIsRenderedWithCorrectFor()
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

	// select based tests

	function testThatSelectElementIsRenderedWithCorrectNameWhenNotSalted()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//select[@name="'.$element->getFieldname().'"]')));
	}

	function testThatSelectElementIsRenderedWithCorrectNameWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//select[@name="'.$element->getFieldname().'"]')));
	}

	function testThatSelectElementIsRenderedWithCorrectIdWhenIsNotSalted()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//select[@id="'.$element->getAlias().'"]')));
	}

	function testThatSelectElementIsRenderedWithCorrectIdWhenIsSalted()
	{
		$element = $this->getElement('name', 'label');
		$element->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//select[@id="'.$element->getAlias().'"]')));
	}

	function testThatCustomAttributesAreRenderedCorrectly()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('class','testClass');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//select[@class="testClass"]')));
	}

	function testThatNotSetCustomAttributesAreNotRendered()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//select[@class]')));
	}

	/* option based tests */

	function testThatOptionsAreRenderedWithCorrectValues()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		foreach($element->getOptions() as $value => $name) {
			$this->assertEquals(1,count($comment=$xml->xpath('//select/option[@value="'.$value.'"]')));
		}
	}

	function testThatOptionsAreRenderedWithCorrectNames()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		foreach($element->getOptions() as $value => $name) {
			$this->assertEquals(1,count($comment=$xml->xpath('//select[option="'.$name.'"]')));
		}
	}


}
