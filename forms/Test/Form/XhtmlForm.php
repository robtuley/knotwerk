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
class T_Test_Form_XhtmlForm extends T_Unit_Case
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
     * Gets a test form.
     *
     * @return T_Form_Get  test form
     */
    protected function getTestForm()
    {
        $form = new T_Form_Get('test','A Test Form');
        $form->setForward(new T_Url('http','example.com',array('te&st')));

        $form->addChild(new T_Form_Fieldset('contact','Cont"act" Details'));
        $form->contact->addChild(new T_Form_Text('name','Name'));
        $form->contact->name->setAttribute('size',50)
                            ->setAttribute('maxlength',50)
                            ->setHelp('some help');

        $form->contact->addChild(new T_Form_Text('email','Em="ail'));
        $form->contact->email->setDefault('test@example.com')
                             ->setOptional();

        return $form;
    }

	function testThatFormIsBuilt()
	{
		$element = $this->getTestForm();
	}

	function testThatFormHasCorrectAction()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);

        $forward = $form->getForward();
        $mode = strpos(_end($forward->getPath()),'.')===false ? T_Url::AS_DIR : null;
        $action = $forward->getUrl(null,$mode);

		$this->assertEquals(1,count($comment=$xml->xpath('//form[@action="'.$action.'"]')));
	}

	function testThatFormHasCorrectId()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(1,count($comment=$xml->xpath('//form[@id="'.$form->getAlias().'"]')));
	}

	function testThatFormHasCorrectMethod()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(1,count($comment=$xml->xpath('//form[@method="'.$form->getMethod().'"]')));
	}

	function testThatFormHasCorrectEnctype()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(1,count($comment=$xml->xpath('//form[@enctype="'.$form->getMimeString().'"]')));
	}

	function testThatFormHasSubmitFieldset()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(1,count($comment=$xml->xpath('//form/fieldset[@class="submit"]')));
	}

	function testThatFormCanHandleMulitpleButtons()
	{
		$form = $this->getTestForm();
        $delete = new T_Form_Button('delete', 'Delete');
        $form->addAction($delete);
        $update = new T_Form_Button('update', 'Update');
        $form->addAction($update);

		$xml = $this->getXmlFrom($form);
		$this->assertEquals(2,count($comment=$xml->xpath('//form/fieldset[@class="submit"]/input')));
	}

	function testThatFormHasDefaultButtons()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(1,count($comment=$xml->xpath('//form/fieldset[@class="submit"]/input[@type="submit"]')));
	}

	function testThatCustomAttributesAreRenderedCorrectly()
	{
		$form = $this->getTestForm();
		$form->setAttribute('class','testClass');
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(1,count($comment=$xml->xpath('//form[@class="testClass"]')));
	}

	function testThatNotSetCustomAttributesAreNotRendered()
	{
		$form = $this->getTestForm();
		$xml = $this->getXmlFrom($form);
		$this->assertEquals(0,count($comment=$xml->xpath('//form[@class]')));
	}
}
