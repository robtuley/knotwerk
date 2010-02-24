<?php
/**
 * Unit test cases for the T_Random class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Random test cases.
 *
 * @package coreTests
 */
class T_Test_Random extends T_Unit_Case
{

    // -- T_Random::createHash()

    function testHashIs32CharsLongByDefault()
    {
        $r = new T_Random();
        $this->assertSame(strlen($r->createHash()),32);
    }

    function testHashIsHexadecimal()
    {
        $r = new T_Random();
        $this->assertTrue(ctype_xdigit($r->createHash()));
    }

    function testGenerateHashShorterThanDefault()
    {
        $r = new T_Random();
        $this->assertTrue(ctype_xdigit($r->createHash(20)));
        $this->assertSame(strlen($r->createHash(20)),20);
    }

    function testGenerateHashLongerThanDefault()
    {
        $r = new T_Random();
        $this->assertTrue(ctype_xdigit($r->createHash(100)));
        $this->assertSame(strlen($r->createHash(100)),100);
    }

    function testRepeatedHashesAreDifferent()
    {
        $r = new T_Random();
        $this->assertNotEquals($r->createHash(),$r->createHash());
    }

    // -- T_Random::createSalt()

    function testSaltIs32CharsLongByDefault()
    {
        $r = new T_Random();
        $this->assertSame(strlen($r->createSalt()),32);
    }

    function testSaltIsAlphaNumeric()
    {
        $r = new T_Random();
        $this->assertTrue(ctype_alnum($r->createSalt()));
    }

    function testGenerateSaltShorterThanDefault()
    {
        $r = new T_Random();
        $this->assertTrue(ctype_alnum($r->createSalt(20)));
        $this->assertSame(strlen($r->createSalt(20)),20);
    }

    function testGenerateSaltLongerThanDefault()
    {
        $r = new T_Random();
        $this->assertTrue(ctype_alnum($r->createSalt(100)));
        $this->assertSame(strlen($r->createSalt(100)),100);
    }

    function testRepeatedSaltsAreDifferent()
    {
        $r = new T_Random();
        $this->assertNotEquals($r->createSalt(),$r->createSalt());
    }

}
