<?php
/**
 * Unit test cases for the T_Exception_TestFail class.
 *
 * @package unitTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_TestFail unit test cases.
 *
 * @package unitTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Exception_TestFail extends T_Unit_Case
{

    function testCanThrowClass()
    {
        $is_thrown = true;
        try {
            throw new T_Exception_TestFail('msg');
            $is_thrown = false;
        } catch (T_Exception_TestFail $e) {
            $this->assertSame('msg',$e->getMessage());
        }
        $this->assertTrue($is_thrown);
    }

}