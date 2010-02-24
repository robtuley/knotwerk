<?php
/**
 * Unit test cases for the T_Exception_Controller class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Exception_Controller test cases.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Exception_Controller extends T_Unit_Case
{

    function testCanThrowClass()
    {
        try {
            throw new T_Exception_Controller('msg');
            $this->fail();
        } catch (T_Exception_Controller $e) {
            $this->assertSame('msg',$e->getMessage());
        }
    }

}