<?php
/**
 * Unit test cases for T_Form_Post class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Post unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Post extends T_Test_Form_ContainerHarness
{

    /**
     * Gets a new instance of the form.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_Post  form to test.
     */
    function getInputCollection($alias,$label)
    {
        return new T_Form_Post($alias,$label);
    }

    function testFormUsesThePostMethod()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame('post',$input->getMethod());
    }

    function testCanAddButtonAction()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $button1 = new T_Form_Button('b1','label');
        $input->addAction($button1);
        $this->assertTrue(in_array($button1,$input->getActions()));
        $button2 = new T_Form_Button('b2','label');
        $input->addAction($button2);
        $this->assertTrue(in_array($button2,$input->getActions()));
    }

    function testActionIsAlwaysAddedByDefault()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $source = new T_Cage_Array(array());
        $this->assertFalse($input->isSubmitted($source));
        $input->validate($source);
        $this->assertFalse($input->isAction('myalias'));
        $source = new T_Cage_Array(array('myalias'=>''));
        $this->assertTrue($input->isSubmitted($source));
        $input->validate($source);
        $this->assertTrue($input->isAction('myalias'));
    }

    function testFormBodyIsNotValidatedIfActionIsNotPresent()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $child = new T_Test_Form_ElementStub('child','label');
        $input->addChild($child);
        $input->validate(new T_Cage_Array(array()));
        $this->assertFalse($child->isValidated());
        $input->validate(new T_Cage_Array(array('myalias'=>'')));
        $this->assertTrue($child->isValidated());
    }

}