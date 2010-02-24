<?php
/**
 * Unit test cases for the T_Exception_Db class.
 *
 * @package dbTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_Db test cases.
 *
 * @package dbTests
 */
class T_Test_Exception_Db extends T_Unit_Case
{

    function testCanBeThrown()
    {
        try {
            throw new T_Exception_Db('msg');
            $this->fail();
        } catch (T_Exception_Db $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}
