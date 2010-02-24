<?php
/**
 * Unit test cases for T_Form_Text class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Text unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Text extends T_Test_Form_ScalarElementHarness
{

    /**
     * Gets a new instance of the text element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_Text  text input to test.
     */
    function getInputElement($alias,$label)
    {
        return new T_Form_Text($alias,$label);
    }

    function testMaxLenNullByDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($input->getAttribute('maxlength'),null);
    }

    function testCanSetSize()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setAttribute('size',53);
        $this->assertSame($input->getAttribute('size'),53);
    }

    function testSetMaxLengthWithNoPriorFilter()
    {
        $input = $this->getInputElement('alias','label');
        $input->setAttribute('maxlength',4);
        $input->validate(new T_Cage_Array(array('alias'=>'12345')));
        $this->assertFalse($input->isValid());
    }

    function testSetMaxLengthWithExistingFilter()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Test_Filter_Suffix('end');
        $input->attachFilter($f)->setAttribute('maxlength',5);
        /* test max length filter */
        $input->validate(new T_Cage_Array(array('alias'=>'123456')));
        $this->assertFalse($input->isValid());
        /* test other filter still exists */
        $input->validate(new T_Cage_Array(array('alias'=>'12345')));
        $this->assertTrue($input->isValid());
        $this->assertSame($input->getValue(),$f->transform('12345'));
    }

    function testChangeMaxLengthOnceAlreadySet()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Test_Filter_Suffix('end');
        $input->attachFilter($f)
              ->setAttribute('maxlength',5)
              ->setAttribute('maxlength',7);
        $input->validate(new T_Cage_Array(array('alias'=>'1234567')));
        $this->assertTrue($input->isValid());
    }

}
