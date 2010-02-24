<?php
/**
 * Unit test cases for the T_Exception_Filter class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_Filter test cases.
 *
 * @package coreTests
 */
class T_Test_Exception_Filter extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Filter('msg');
            $this->fail();
        } catch (T_Exception_Filter $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}