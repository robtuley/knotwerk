<?php
/**
 * Unit test cases for the T_Exception_Command class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_Command test cases.
 *
 * @package coreTests
 */
class T_Test_Exception_Command extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Command('msg');
            $this->fail();
        } catch (T_Exception_Command $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}