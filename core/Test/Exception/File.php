<?php
/**
 * Unit test cases for the T_Exception_File class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_File test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Exception_File extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_File('a_file.txt');
            $this->fail();
        } catch (T_Exception_File $e) {
            $this->assertContains('a_file.txt',$e->getMessage());
        }
    }

    function testIncludesOptionalMessage()
    {
        try {
            throw new T_Exception_File('a_file.txt','msg');
            $this->fail();
        } catch (T_Exception_File $e) {
            $this->assertContains('msg',$e->getMessage());
        }
    }

    function testCodeIsSetInConstructor()
    {
        try {
            throw new T_Exception_File('a_file.txt','msg',23);
            $this->fail();
        } catch (T_Exception_File $e) {
            $this->assertSame(23,$e->getCode());
        }
    }

}