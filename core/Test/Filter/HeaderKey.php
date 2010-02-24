<?php
/**
 * Unit test cases for the T_Filter_HeaderKey class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_HeaderKey test cases.
 *
 * @package coreTests
 */
class T_Test_Filter_HeaderKey extends T_Test_Filter_SkeletonHarness
{

    function testLeavesCorrectlyCasedKeysIntact()
    {
        $filter = new T_Filter_HeaderKey();
        $this->assertSame($filter->transform('Host'),'Host');
        $this->assertSame($filter->transform('Content-Type'),'Content-Type');
    }

    function testCorrectsCaseOnSingleWordKeys()
    {
        $filter = new T_Filter_HeaderKey();
        $this->assertSame($filter->transform('hOSt'),'Host');
    }

    function testCorrectsCaseOnTwoWordKeys()
    {
        $filter = new T_Filter_HeaderKey();
        $this->assertSame($filter->transform('cONTent-tYPE'),'Content-Type');
    }

    function testCanApplyPriorFilter()
    {
        $filter = new T_Filter_HeaderKey('mb_trim');
        $this->assertSame($filter->transform(' cONTent-tYPE '),'Content-Type');
    }

}