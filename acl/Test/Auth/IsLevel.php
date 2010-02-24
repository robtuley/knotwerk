<?php
/**
 * Unit test cases for the T_Auth_IsLevel class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Auth_IsLevel unit test cases.
 *
 * @package aclTests
 */
class T_Test_Auth_IsLevel extends T_Unit_Case
{

    function testNotSatisfiedWhenDifferentLevels()
    {
        $spec = new T_Auth_IsLevel(T_Auth::CHALLENGED);
        $diff = array(
                        T_Auth::TOKEN,
                        T_Auth::OBFUSCATED,
                        T_Auth::HUMAN,
                        T_Auth::NONE
                     );
        foreach ($diff as $type) {
            $this->assertFalse($spec->isSatisfiedBy(new T_Auth($type)));
        }
    }

    function testIsSatisfiedWhenSameLevel()
    {
        $levels = array(
                        T_Auth::CHALLENGED,
                        T_Auth::TOKEN,
                        T_Auth::OBFUSCATED,
                        T_Auth::HUMAN
                     );
        foreach ($levels as $type) {
            $spec = new T_Auth_IsLevel($type);
            $this->assertTrue($spec->isSatisfiedBy(new T_Auth($type)));
        }
    }

    function testCanUseBinaryOperatorToSpecifyMultipleMatches()
    {
        $level = T_Auth::USER|T_Auth::HUMAN;
        $spec = new T_Auth_IsLevel($level);
        $this->assertTrue($spec->isSatisfiedBy(new T_Auth(T_Auth::CHALLENGED)));
        $this->assertTrue($spec->isSatisfiedBy(new T_Auth(T_Auth::TOKEN)));
        $this->assertTrue($spec->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        $this->assertFalse($spec->isSatisfiedBy(new T_Auth(T_Auth::OBFUSCATED)));
        $this->assertFalse($spec->isSatisfiedBy(new T_Auth(T_Auth::NONE)));
    }

}