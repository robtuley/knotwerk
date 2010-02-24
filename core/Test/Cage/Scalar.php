<?php
/**
 * Unit test cases for the T_Cage_Scalar class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Cage_Scalar test cases.
 *
 * This class defines a series of unit tests for the T_Cage_Scalar class.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Cage_Scalar extends T_Unit_Case
{

    function testCageThenUncageData()
    {
        $data  = 'some suspect data';
        $cage = new T_Cage_Scalar($data);
        $test  = $cage->uncage();
        $this->assertSame($test,$data);
    }

    function testCanFilterDataOnExtraction()
    {
        $f = new T_Test_Filter_Suffix();
        $cage = new T_Cage_Scalar('data');
        $expect = $f->transform('data');
        $test = $cage->filter($f)->uncage();
        $this->assertSame($test,$expect);
    }

}