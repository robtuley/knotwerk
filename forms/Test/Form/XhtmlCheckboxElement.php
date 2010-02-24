<?php
class T_Test_Form_XhtmlCheckboxElement extends T_Unit_Case
{

    function getXmlFrom($element)
    {
    	$visitor = new T_Form_Xhtml();
    	$element->accept($visitor);
    	return new SimpleXMLElement('<fragment>'.$visitor->__toString().'</fragment>');
    }

    function getElement($name,$label)
    {
    	$element = new T_Form_Checkbox($name,$label);
    	$element->setOptions(array('test'=>'1','dave'=>'fred','swadg'=>'asdgf'));
		return $element;
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
		$this->assertEquals(1,count($comment=$xml->xpath('//legend/span/em')));
	}

	function testThatLegendIsRenderedWithASpanAndCorrectLabelWhenIsOptional()
	{
		$element = $this->getElement('name', 'label');
		$element->setOptional();
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(1,count($comment=$xml->xpath('//legend[span="label"]')));
	}

	/* input and label based tests */

	function testThatInputIsRenderedWithCorrectTypeAndCorrectNumberOfOptions()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(count($element->getOptions()),count($comment=$xml->xpath('//input[@type="checkbox"]')));
	}

	function testThatInputsAreRenderedWithCorrectNames()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(count($element->getOptions()),count($comment=$xml->xpath('//input[@name="'.$element->getFieldname().'[]"]')));
	}

	function testThatInputsAreRenderedWithCorrectIds()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		for($i=0; $i < (count($element->getOptions())); $i++) {
			$this->assertEquals(1,count($comment=$xml->xpath('//input[@id="'.$element->getAlias().'__'.$i.'"]')));
		}
	}

	function testThatInputsAreRenderedWithCorrectIdsWhenIdIsOverwritten()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('id','dave');
		$xml = $this->getXmlFrom($element);
		for($i=0; $i < (count($element->getOptions())); $i++) {
			$this->assertEquals(1,count($comment=$xml->xpath('//input[@id="'.$element->getAttribute('id').'__'.$i.'"]')));
		}
	}

	function testThatInputsAreRenderedWithCorrectValues()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		foreach($element->getOptions() as $value => $label) {
			$this->assertEquals(1,count($comment=$xml->xpath('//input[@value="'.$value.'"]')));
		}
	}

	function testThatLabelsAreRenderedWithCorrectFors()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		for($i=0; $i < (count($element->getOptions())); $i++) {
			$this->assertEquals(1,count($comment=$xml->xpath('//label[@for="'.$element->getFieldname().'__'.$i.'"]')));
		}
	}

	function testThatInputsAreRenderedWithCorrectLabels()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		foreach($element->getOptions() as $value => $label) {
			$this->assertEquals(1,count($comment=$xml->xpath('//fieldset[label="'.$label.'"]')));
		}
	}

	function testThatCustomAttributesAreRenderedCorrectly()
	{
		$element = $this->getElement('name', 'label');
		$element->setAttribute('class','testClass');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(count($element->getOptions()),count($comment=$xml->xpath('//input[@class="testClass"]')));
	}

	function testThatNotSetCustomAttributesAreNotRendered()
	{
		$element = $this->getElement('name', 'label');
		$xml = $this->getXmlFrom($element);
		$this->assertEquals(0,count($comment=$xml->xpath('//input[@class]')));
	}
}