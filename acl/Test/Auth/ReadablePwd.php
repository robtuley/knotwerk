<?php
/**
 * Unit test cases for the T_Auth_ReadablePwd class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Auth_ReadablePwd unit test cases.
 *
 * @package aclTests
 */
class T_Test_Auth_ReadablePwd extends T_Unit_Case
{

    function testThatCreateMethodGeneratesATextString()
    {
        $factory = new T_Auth_ReadablePwd();
        $pwd = $factory->create();
        $this->assertTrue(is_string($pwd));
        $this->assertTrue(strlen($pwd)>0);
    }

    function testThatCreateMethodGeneratesDifferentStringOnEachCall()
    {
        $factory = new T_Auth_ReadablePwd();
        $this->assertNotEquals($factory->create(),$factory->create());
    }

    function testThatALargerNumberOfSyllablesGivesALongerPassword()
    {
        $factory = new T_Auth_ReadablePwd(1);
        $short = $factory->create();
        $factory = new T_Auth_ReadablePwd(20);
        $long = $factory->create();
        $this->assertTrue(strlen($long)>strlen($short));
    }

    function testRepeatedGenerationDoesNotFail()
    {
        // some code (for exmaple the consonant doubling) only gets
        // executed with some probability. We generate 100 passwords here to
        // make it as likely as possible this code is always covered by the
        // test suite.
        $factory = new T_Auth_ReadablePwd();
        for ($i=0;$i<100;$i++) {
            $factory->create();
        }
    }

}