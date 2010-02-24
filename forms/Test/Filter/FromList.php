<?php
/**
 * Unit test cases for the T_Filter_FromList class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_FromList test cases.
 *
 * @package formTests
 */
class T_Test_Filter_FromList extends T_Test_Filter_SkeletonHarness
{

    function testPutsASingleValueIntoAnArray()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array('value'),$filter->transform('value'));
    }

    function testTrimsASingleValue()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array('value'),$filter->transform('  value  '));
    }

    function testSeparatesAndTrimsMultipleValues()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array('a','b','c'),$filter->transform('a ,b, c '));
    }

    function testProducesEmptyArrayWithEmptyString()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array(),$filter->transform(''));
    }

    function testRemovesEmptyElementAtStartOfString()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array('a','b'),$filter->transform(' ,a,b'));
    }

    function testRemovesEmptyElementAtEndOfString()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array('a','b'),$filter->transform('a,b, '));
    }

    function testRemovesEmptyElementInMiddleOfString()
    {
        $filter = new T_Filter_FromList();
        $this->assertSame(array('a','b'),$filter->transform('a, ,b'));
    }

    function testCanPipePriorFilter()
    {
        $filter = new T_Filter_FromList('mb_strtoupper');
        $this->assertSame(array('VALUE'),$filter->transform('value'));
    }

}