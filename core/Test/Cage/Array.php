<?php
/**
 * Unit test cases for the T_Cage_Array class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Cage_Array test cases.
 *
 * This class defines a series of unit tests for the T_Cage_Array class.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Cage_Array extends T_Unit_Case
{

    function testCageThenUncage()
    {
        $expect = array('a','b'=>'c',4);
        $cage = new T_Cage_Array($expect);
        $this->assertSame($expect,$cage->uncage());
    }

    function testCanExtractScalar()
    {
        $data = array('a','b'=>'c',4);
        $cage = new T_Cage_Array($data);
        $test = $cage->asScalar('b');
        $this->assertTrue($test instanceof T_Cage_Scalar);
        $this->assertSame($test->uncage(),$data['b']);
    }

    function testCanExtractObjectAsScalar()
    {
        $obj = new T_Pattern_Regex('/test/');
        $data = array('b'=>$obj);
        $cage = new T_Cage_Array($data);
        $test = $cage->asScalar('b');
        $this->assertTrue($test instanceof T_Cage_Scalar);
        $this->assertSame($test->uncage(),$obj);
    }

    function testScalarExtractFailsWhenKeyDoesNotExist()
    {
        $data = array('a',4);
        $cage = new T_Cage_Array($data);
        try {
            $test = $cage->asScalar('b');
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testScalarExtractFailsWhenIsArray()
    {
        $data = array('b'=> array(1,2));
        $cage = new T_Cage_Array($data);
        try {
            $test = $cage->asScalar('b');
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testCanExtractNestedArray()
    {
        $data = array('a','b'=>array(1,2),3);
        $cage = new T_Cage_Array($data);
        $test = $cage->asArray('b');
        $this->assertTrue($test instanceof T_Cage_Array);
        $this->assertSame($test->uncage(),$data['b']);
    }

    function testArrayExtractFailsWhenKeyDoesNotExist()
    {
        $data = array('a',4);
        $cage = new T_Cage_Array($data);
        try {
            $test = $cage->asArray('b');
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testArrayExtractFailsWhenIsScalar()
    {
        $data = array('b'=>1);
        $cage = new T_Cage_Array($data);
        try {
            $test = $cage->asArray('b');
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testIsset()
    {
        $cage = new T_Cage_Array(array('b'=>1));
        $this->assertTrue($cage->exists('b'));
        $this->assertFalse($cage->exists('a'));
    }

    function testCanFilterDataOnExtraction()
    {
        $f = new T_Test_Filter_ArrayPrefix();
        $data = array('One','Two');
        $cage = new T_Cage_Array($data);
        $expect = $f->transform($data);
        $test = $cage->filter($f)->uncage();
        $this->assertSame($test,$expect);
    }

}