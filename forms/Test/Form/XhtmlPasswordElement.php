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
 * T_Form_Xhtml unit tests for the T_Form_Password element.
 *
 * @package formTests
 */
class T_Test_Form_XhtmlPasswordElement extends T_Test_Form_XhtmlTextElement
{
    
    /**
     * Get the test element.
     * 
     * @return T_Form_Text
     */
    function getElement($name,$label)
    {
    	return new T_Form_Password($name,$label);
    }

	function testThatInputElementIsRenderedWithCorrectType()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//input[@type="password"]')));
	}

}