<?php
/**
 * Unit test cases for the T_Filter_NoMagicQuotes class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_NoMagicQuotes test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_NoMagicQuotes extends T_Test_Filter_SkeletonHarness
{

    function testStripSlashesFromSingleValue()
    {
        $filter = new T_Filter_NoMagicQuotes();
        $test = 'some\\\'quotes\\"';
        $expect = 'some\'quotes"';
        $this->assertSame($filter->transform($test),$expect);
    }

    function testStripSlashesFromArray()
    {
        $filter = new T_Filter_NoMagicQuotes();
        $test = array('some\\\'quotes\\"','more\\\'quotes\\"');
        $expect = array('some\'quotes"','more\'quotes"');
        $this->assertSame($filter->transform($test),$expect);
    }

    function testStripSlashesFromArrayKeys()
    {
        $filter = new T_Filter_NoMagicQuotes();
        $test = array('some\\\'quotes\\"'=>'val1','more\\\'quotes\\"'=>'val2');
        $expect = array('some\'quotes"'=>'val1','more\'quotes"'=>'val2');
        $this->assertSame($filter->transform($test),$expect);
    }

    function testStripSlashesFromNestedArray()
    {
        $filter = new T_Filter_NoMagicQuotes();
        $test = array('some\\\'quotes\\"',
                      array('more\\\'quotes\\"','extra\\\'quotes\\"') );
        $expect = array('some\'quotes"',
                        array('more\'quotes"','extra\'quotes"') );
        $this->assertSame($filter->transform($test),$expect);
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Filter_NoMagicQuotes('mb_trim');
        $this->assertSame($filter->transform(' no\\\'Quote '),'no\'Quote');
    }

}
