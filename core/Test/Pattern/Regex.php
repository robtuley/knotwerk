<?php
/**
 * Unit test cases for the T_Pattern_Regex class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Pattern_Regex test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Pattern_Regex extends T_Unit_Case
{

    /**
     * Initialised T_Pattern_Regex object.
     *
     * This variable is created during the common setUp() method for all tests
     * and is an initialized T_Pattern_Regex object.
     *
     * @var object
     */
    protected $regex;

    /**
     * Creates T_Pattern_Regex object.
     *
     * This function creates the initial test environment for all the unit tests
     * defined in this class, and produces an initialised T_Pattern_Regex object.
     */
    function setUp()
    {
        $this->regex = new T_Pattern_Regex('/t([a-z])(\d)t/');
    }

    /**
     * Test the isMatch() method.
     *
     * Checks that the isMatch() method produces the correct boolean response.
     */
    function testIsMatch()
    {
        // no matches
        $this->assertFalse( $this->regex->isMatch('t3t tA5t t2at tig') );
        // single match
        $this->assertTrue( $this->regex->isMatch('t3t ta4t t2at tig') );
        // double match
        $this->assertTrue( $this->regex->isMatch('t3t ta4t t2at tig t6et') );
    }

    /**
     * Test the getFirstMatch() method.
     *
     * Checks that the getFirstMatch() method returns the correct array
     * components.
     */
    function testGetFirstMatch()
    {
        $expected = array('ta4t','a','4');
        // no matches
        $this->assertFalse( $this->regex->getFirstMatch('t3t tA2t t2at tig') );
        // single match
        $this->assertSame($expected,
                            $this->regex->getFirstMatch('t3t ta4t t2at tig') );
        // double match
        $this->assertSame($expected,
                            $this->regex->getFirstMatch('t3t ta4t t2at te5t') );
    }

    /**
     * Test the getAllMatch() method.
     *
     * Checks the getAllMatch() method produces the correct 2D array. The
     * routine should be case-sensitive, and so on.
     */
    function testGetAllMatch()
    {
        $expected_single = array( 0 => array('ta4t'),
                                  1 => array('a'),
                                  2 => array('4') );
        $expected_double = array( 0 => array('ta4t','te5t'),
                                  1 => array('a','e'),
                                  2 => array('4','5') );
        // no matches
        $this->assertFalse( $this->regex->getAllMatch('t3t tA2t t2at tig') );
        // single match
        $this->assertSame($expected_single,
                            $this->regex->getAllMatch('t3t ta4t t2at tig') );
        // double match
        $this->assertSame($expected_double,
                            $this->regex->getAllMatch('t3t ta4t t2at te5t') );
    }

    /**
     * Resets the test setup between tests.
     *
     * This routine destroys the actions undertaken during the test setUp()
     * routine, and in this case simply unsets the created regex object.
     */
    protected function tearDown()
    {
        unset($this->regex);
    }

}