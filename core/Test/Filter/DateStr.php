<?php
/**
 * Unit test cases for the T_Filter_DateStr class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_DateStr test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_DateStr extends T_Test_Filter_SkeletonHarness
{

    function testConvertsIntegerToDateWithFormatString()
    {
        $filter = new T_Filter_DateStr('m.d.y');
        $now = time();
        $expected = date('m.d.y',$now);
        $this->assertSame($filter->transform($now),$expected);
    }

    function testConvertsIntegerToDateWithFormatConstant()
    {
        $filter = new T_Filter_DateStr(DATE_ATOM);
        $now = time();
        $expected = date(DATE_ATOM,$now);
        $this->assertSame($filter->transform($now),$expected);
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Validate_UnixDate('d|m|y');
        $filter = new T_Filter_DateStr(DATE_ATOM,$pipe);
        $time = $pipe->transform('17/02/2002');
        $expected = date(DATE_ATOM,$time);
        $this->assertSame($filter->transform('17/02/2002'),$expected);
    }

}