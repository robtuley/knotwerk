<?php
/**
 * Unit test cases for T_Form_Xhtml class.
 *
 * @package formTests
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Xhtml unit tests for the T_Form_Radio element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlRadioElement extends T_Test_Form_XhtmlCheckboxElement
{

    /**
     * Get the test element.
     *
     * @return T_Form_Text
     */
    function getElement($name,$label)
    {
    	$element = new T_Form_Radio($name,$label);
    	$element->setOptions(array('test'=>'1','dave'=>'fred', 'swadg' => 'asdgf'));
		return $element;
    }

	function testThatInputIsRenderedWithCorrectTypeAndCorrectNumberOfOptions()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(count($element->getOptions()),count($comment=$xml->xpath('//input[@type="radio"]')));
	}

	function testThatInputsAreRenderedWithCorrectNames()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(count($element->getOptions()),count($comment=$xml->xpath('//input[@name="'.$element->getFieldname().'"]')));
	}

}
