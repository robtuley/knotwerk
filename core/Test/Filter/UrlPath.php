<?php
class T_Test_Filter_UrlPath extends T_Test_Filter_SkeletonHarness
{

    function testExplodePathOnForwardSlash()
    {
        $filter = new T_Filter_UrlPath();
        $test = 'some/path/file.php';
        $expect = array('some','path','file.php');
        $this->assertSame($filter->transform($test),$expect);
    }

    function testMaintainsCaseOfPathSegments()
    {
        $filter = new T_Filter_UrlPath();
        $test = 'soMe/miXeD/CAsE';
        $expect = array('soMe','miXeD','CAsE');
        $this->assertSame($filter->transform($test),$expect);
    }

    function testStripsStartAndTrailingForwardSlash()
    {
        $filter = new T_Filter_UrlPath();
        $test = '/some/path/';
        $expect = array('some','path');
        $this->assertSame($filter->transform($test),$expect);
    }

    function testStripsAnchorFromEndOfPath()
    {
        $filter = new T_Filter_UrlPath();
        $test1 = 'some/path#anchor';
        $test2 = 'some/path/#anchor';
        $test3 = 'some/path#';
        $expect = array('some','path');
        $this->assertSame($filter->transform($test1),$expect);
        $this->assertSame($filter->transform($test2),$expect);
        $this->assertSame($filter->transform($test3),$expect);
    }

    /**
     * Test strips parameters from end of path.
     */
    function testStripsParametersFromEndOfPath()
    {
        $filter = new T_Filter_UrlPath();
        $test1 = 'some/path?name=value';
        $test2 = 'some/path/?name=value&name2=value2';
        $test3 = 'some/path?';
        $expect = array('some','path');
        $this->assertSame($filter->transform($test1),$expect);
        $this->assertSame($filter->transform($test2),$expect);
        $this->assertSame($filter->transform($test3),$expect);
    }

    /**
     * Test strips both anchors and parameters from path.
     */
    function testStripsBothParametersAndAnchor()
    {
        $filter = new T_Filter_UrlPath();
        $test = '/some/path?name=value#anchor';
        $expect = array('some','path');
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test single segment path.
     */
    function testPathWithSingleSegment()
    {
        $filter = new T_Filter_UrlPath();
        $test1 = 'path';
        $test2 = '/path/';
        $expect = array('path');
        $this->assertSame($filter->transform($test1),$expect);
        $this->assertSame($filter->transform($test2),$expect);
    }

    /**
     * Test zero segment path.
     */
    function testPathWithZeroSegment()
    {
        $filter = new T_Filter_UrlPath();
        $test1 = '';
        $test2 = '/';
        $test3 = '/?name=value#anchor';
        $expect = array();
        $this->assertSame($filter->transform($test1),$expect);
        $this->assertSame($filter->transform($test2),$expect);
        $this->assertSame($filter->transform($test3),$expect);
    }

    /**
     * Test that the segments are decoded.
     */
    function testSegmentsAreUrlDecoded()
    {
        $filter = new T_Filter_UrlPath();
        $test = 'a%20path/with-symbols/in+it';
        $expect = array('a path','with-symbols','in+it');
        $this->assertSame($filter->transform($test),$expect);
    }

    /**
     * Test can pipe prior filter.
     */
    function testCanApplyPriorFilter()
    {
        $filter = new T_Filter_UrlPath('mb_strtolower');
        $test = 'soMe/miXeD/CAsE';
        $expect = array('some','mixed','case');
        $this->assertSame($filter->transform($test),$expect);
    }

}