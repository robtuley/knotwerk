<?php
/**
 * Unit test cases for T_Form_TextArea class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_TextArea unit tests.
 *
 * @package formTests
 */
class T_Test_Form_TextArea extends T_Test_Form_ScalarElementHarness
{

    /**
     * Gets a new instance of the text element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_TextArea  text input to test.
     */
    function getInputElement($alias,$label)
    {
        return new T_Form_TextArea($alias,$label);
    }

}
