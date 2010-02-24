<?php
/**
 * Unit test cases for the T_Exception_UploadedFile class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_UploadedFile test cases.
 *
 * @package coreTests
 */
class T_Test_Exception_UploadedFile extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_UploadedFile('msg');
            $this->fail();
        } catch (T_Exception_UploadedFile $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}