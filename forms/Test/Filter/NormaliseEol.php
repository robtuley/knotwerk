<?php
/**
 * Unit test cases for the T_Filter_NormaliseEol class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_NormaliseEol unit test cases.
 *
 * @package formTests
 */
class T_Test_Filter_NormaliseEol extends T_Test_Filter_SkeletonHarness
{

    function testNoEolLeavesInputUnAffected()
    {
        $filter = new T_Filter_NormaliseEol();
        $this->assertSame('single line',$filter->transform('single line'));
    }

    function testLeavesDefaultEolUnAffected()
    {
        $filter = new T_Filter_NormaliseEol();
        $this->assertSame('multi'.EOL.'line',$filter->transform('multi'.EOL.'line'));
    }

    function testConvertsWindowsEolToDefaultEol()
    {
        $filter = new T_Filter_NormaliseEol();
        $this->assertSame('multi'.EOL.'line',$filter->transform("multi\r\nline"));
    }

    function testConvertsUnixEolToDefaultEol()
    {
        $filter = new T_Filter_NormaliseEol();
        $this->assertSame('multi'.EOL.'line',$filter->transform("multi\nline"));
    }

    function testCanHandleUnicodeWhenSearchingForLineBreaks()
    {
        $filter = new T_Filter_NormaliseEol();
        $this->assertSame('Iñtërnâtiôn'.EOL.'àlizætiøn',$filter->transform("Iñtërnâtiôn\r\nàlizætiøn"));
    }

    function testCanSpecifyLineBreakCharToNormaliseTo()
    {
        $filter = new T_Filter_NormaliseEol("\r\n");
        $this->assertSame($filter->transform("multi\nline"),"multi\r\nline");
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix();
        $filter = new T_Filter_NormaliseEol(EOL,$pipe);
        $this->assertSame($filter->transform("multi\r\nline"),$pipe->transform('multi'.EOL.'line'));
    }

}
