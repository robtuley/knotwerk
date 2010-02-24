<?php
/**
 * Unit test cases for the T_Exception_Email class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_Email test cases.
 *
 * @package coreTests
 */
class T_Test_Exception_Email extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Email('msg');
            $this->fail();
        } catch (T_Exception_Email $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}