<?php
/**
 * Unit test cases for T_Form_Get class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Get unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Get extends T_Test_Form_ContainerHarness
{

    /**
     * Gets a new instance of the form.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_Get  form to test.
     */
    function getInputCollection($alias,$label)
    {
        return new T_Form_Get($alias,$label);
    }

    /**
     * Test uses the GET method.
     */
    function testFormUsesTheGetMethod()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame('get',$input->getMethod());
    }

}