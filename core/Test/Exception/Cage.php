<?php
/**
 * Unit test cases for the T_Exception_Cage class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_Cage test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Exception_Cage extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Cage('msg');
            $this->fail();
        } catch (T_Exception_Cage $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}