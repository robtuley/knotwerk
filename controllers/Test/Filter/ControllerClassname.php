<?php
/**
 * Unit test cases for the T_Filter_ControllerClassname class.
 *
 * @package controllerTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_ControllerClassname test cases.
 *
 * @package controllerTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_ControllerClassname extends T_Test_Filter_SkeletonHarness
{

    /**
     * Test single mixed case segment.
     */
    function testSingleMixedCaseSegment()
    {
        $filter = new T_Filter_ControllerClassname();
        $test = 'cAPItaLised';
        $expect = 'Capitalised';
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test splits on space, +, - and _
     */
    function testUcfirstOnSpacePlusDashUnderscore()
    {
        $filter = new T_Filter_ControllerClassname();
        $test = 'this-should be+ucfirst_ed';
        $expect = 'ThisShouldBeUcfirstEd';
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test repeated delimiters within segment
     */
    function testRepeatedDelimitersWithinSegment()
    {
        $filter = new T_Filter_ControllerClassname();
        $test = 'this---should _ be++ucfirst  _ed';
        $expect = 'ThisShouldBeUcfirstEd';
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test repeated delimiters within segment
     */
    function testTrailingAndStartingDelimitersWithinSegment()
    {
        $filter = new T_Filter_ControllerClassname();
        $test = '-this-should be+ucfirst_ed+ ';
        $expect = 'ThisShouldBeUcfirstEd';
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test international text.
     */
    function testSegmentWithi18nText()
    {
        $filter = new T_Filter_ControllerClassname();
        $test   = 'iñt ër_nâtiôn+àLIZ-æTIØN';
        $expect = 'IñtËrNâtiônÀlizÆtiøn';
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test can pipe prior filter through.
     */
    function testCanApplyPriorFilter()
    {
        $filter = new T_Filter_ControllerClassname('mb_trim');
        $this->assertSame($filter->transform(' prior filter '),'PriorFilter');
    }


}