<?php
/**
 * Unit test cases for T_Form_Error class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Error unit test harness.
 *
 * @package formTests
 */
class T_Test_Form_Error extends T_Unit_Case
{

    function testErrorMessageSetInConstructor()
    {
        $error = new T_Form_Error('some message');
        $this->assertSame('some message',$error->getMessage());
    }

}