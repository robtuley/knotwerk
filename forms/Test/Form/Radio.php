<?php
/**
 * Unit test cases for T_Form_Radio class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Radio unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Radio extends T_Test_Form_ScalarElementHarness
{

    /**
     * Gets a new instance of the radio element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_Radio  text input to test.
     */
    function getInputElement($alias,$label)
    {
        $input = new T_Form_Radio($alias,$label);
        $input->setOptions(array('value1'=>'Label 1','value2'=>'Label 2','value3'=>'Label 3'));
        return $input;
    }

    function testNoOptionsByDefault()
    {
        $input = new T_Form_Radio('myalias','mylabel');
        $this->assertSame($input->getOptions(),array());
    }

    function testSetOptionsMethodHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setOptions(array(1,2,3));
        $this->assertSame($test,$input);
    }

    function testSetOptionsStoresOptionsLocally()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $expect = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($expect);
        $this->assertSame($input->getOptions(),$expect);
    }

    function testSetOptionsWithNoPriorFilter()
    {
        $input = $this->getInputElement('alias','label');
        $options = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($options);
        $input->validate(new T_Cage_Array(array('alias'=>'uk')));
        $this->assertTrue($input->isValid());
        $input->validate(new T_Cage_Array(array('alias'=>'us')));
        $this->assertFalse($input->isValid());
    }

    function testSetOptionsWithExistingFilter()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Test_Filter_Suffix();
        $options = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($options);
        $input->attachFilter($f)->setOptions($options);
        /* test options filter */
        $input->validate(new T_Cage_Array(array('alias'=>'us')));
        $this->assertFalse($input->isValid());
        /* test other filter still exists */
        $input->validate(new T_Cage_Array(array('alias'=>'uk')));
        $this->assertTrue($input->isValid());
        $this->assertSame($input->getValue(),$f->transform('uk'));
    }

    function testChangeOptionsOnceAlreadySet()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Test_Filter_Suffix();
        $options1 = array('uk'=>'Britain');
        $options2 = array('fr'=>'France');
        $input->attachFilter($f)
              ->setOptions($options1)
              ->setOptions($options2);
        $input->validate(new T_Cage_Array(array('alias'=>'fr')));
        $this->assertTrue($input->isValid());
        $input->validate(new T_Cage_Array(array('alias'=>'uk')));
        $this->assertFalse($input->isValid());
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

    function testDefaultMaintainedWhenAlsoInNewOptions()
    {
        $input = $this->getInputElement('alias','label');
        $options1 = array(1=>'one',2=>'two');
        $options2 = array(2=>'two',3=>'three');
        $input->setOptions($options1)
              ->setDefault(2)
              ->setOptions($options2);
        $this->assertSame(2,$input->getDefault());
    }

    function testDefaultClearedWhenNotInNewOptions()
    {
        $input = $this->getInputElement('alias','label');
        $options1 = array(1=>'one',2=>'two');
        $options2 = array(2=>'two',3=>'three');
        $input->setOptions($options1)
              ->setDefault(1)
              ->setOptions($options2);
        $this->assertSame(null,$input->getDefault());
    }

}