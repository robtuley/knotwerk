<?php
/**
 * Unit test cases for T_Form_Select class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Select unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Select extends T_Test_Form_Radio
{

    /**
     * Gets a new instance of the select element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_Select  text input to test.
     */
    function getInputElement($alias,$label)
    {
        $input = new T_Form_Select($alias,$label);
        $input->setOptions(array('value1'=>'Label 1',
                                 'value2'=>'Label 2',
                                 'value3'=>'Label 3'));
        return $input;
    }

    function testExtraNotPresentLabelAddedToOptions()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $expect = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($expect,'Not Present');
        $test = $input->getOptions();
        $this->assertTrue($test['uk']==='Britain');
        $this->assertTrue($test['fr']==='France');
        $this->assertTrue(in_array('Not Present',$test,true));
    }

    function testExtraNotPresentLabelSetAsDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $expect = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($expect,'Not Present');
        $test = $input->getOptions();
        $this->assertTrue($test[$input->getDefault()]==='Not Present');
    }

    function testExtraNotPresentLabelDoesNotOverrideAnExistingDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $expect = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($expect)
              ->setDefault('uk')
              ->setOptions($expect,'Not Present');
        $test = $input->getOptions();
        $this->assertSame('uk',$input->getDefault());
    }

    function testExceptionThrownIfNameClashWithNotPresentKey()
    {
        $input = $this->getInputElement('myalias','mylabel');
        try {
            $input->setOptions(array('not_present'=>'Key Clash'),'Not Present');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testPresenceOfNotPresentKeyResultsInNotSubmittedTrue()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptions(array('uk'=>'Britain','fr'=>'France'),'Not Present');
        $this->assertFalse($input->isSubmitted(new T_Cage_Array(array('not_present'=>'value'))));
    }

    function testSubmissionOfNotPresentKeyResultsInNotPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptions(array('uk'=>'Britain','fr'=>'France'),'Not Present');
        $input->validate(new T_Cage_Array(array('not_present'=>'value')));
        $this->assertFalse($input->isPresent());
    }

    function testAttackWithOutOfRangeValuesResultsInNoDefault()
    {
        $input = $this->getInputElement('alias','label');
        $input->redisplayInvalid(true)
              ->redisplayValid(true)
              ->setOptions(array(2=>'first',5=>'second'))
              ->setDefault(2);
        $input->validate(new T_Cage_Array(array('alias'=>3)));
        $this->assertFalse($input->isValid());
        $this->assertSame($input->getDefault(),null);
    }

    function testCannotSetDefaultNotInOptions()
    {
        $input = $this->getInputElement('alias','label');
        try {
            $input->setDefault('notanoption');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

}