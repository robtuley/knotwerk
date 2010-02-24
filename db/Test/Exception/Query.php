<?php
/**
 * Unit test cases for the T_Exception_Query class.
 *
 * @package dbTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_NoResult test cases.
 *
 * @package dbTests
 */
class T_Test_Exception_Query extends T_Unit_Case
{

    function testCanBeThrown()
    {
        try {
            throw new T_Exception_Query(null,'msg');
            $this->fail();
        } catch (T_Exception_Query $e) { }
    }

    function testCaughtByGeneralDbExceptionClass()
    {
        try {
            throw new T_Exception_Query(null,'msg');
            $this->fail();
        } catch (T_Exception_Db $e) { }
    }

    function testMessageAndCodePreservedInConstructor()
    {
        try {
            throw new T_Exception_Query(null,'msg',13);
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertSame('msg',$e->getMessage());
            $this->assertSame(13,$e->getCode());
        }
    }



}
