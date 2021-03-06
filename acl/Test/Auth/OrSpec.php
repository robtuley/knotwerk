<?php
/**
 * Unit test cases for the T_Auth_OrSpec class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Auth_OrSpec unit test cases.
 *
 * @package aclTests
 */
class T_Test_Auth_OrSpec extends T_Unit_Case
{

    function testFalseFalseReturnsFalse()
    {
        $spec = new T_Auth_OrSpec(new T_Test_Auth_SpecStub(false),
                                    new T_Test_Auth_SpecStub(false) );
        $this->assertSame($spec->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)),false);
    }

    function testFalseTrueReturnsTrue()
    {
        $spec = new T_Auth_OrSpec(new T_Test_Auth_SpecStub(false),
                                    new T_Test_Auth_SpecStub(true) );
        $this->assertSame($spec->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)),true);
    }

    function testTrueFalseReturnsTrue()
    {
        $spec = new T_Auth_OrSpec(new T_Test_Auth_SpecStub(true),
                                    new T_Test_Auth_SpecStub(false) );
        $this->assertSame($spec->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)),true);
    }

    function testTrueTrueReturnsTrue()
    {
        $spec = new T_Auth_OrSpec(new T_Test_Auth_SpecStub(true),
                                    new T_Test_Auth_SpecStub(true) );
        $this->assertSame($spec->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)),true);
    }

}