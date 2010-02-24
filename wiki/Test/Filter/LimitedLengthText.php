<?php
/**
 * Unit test cases for the T_Filter_LimitedLengthText class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_LimitedLengthText unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Filter_LimitedLengthText extends T_Unit_Case
{

    function testSingleStringNotChangedWhenLessThanMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(10,'\s','...');
        $this->assertSame('content',$limit->transform('content'));
    }

    function testMultileStringsNotChangedWhenTotalLessThanMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(20,'\s','...');
        $this->assertSame('content1',$limit->transform('content1'));
        $this->assertSame('content2',$limit->transform('content2'));
    }

    function testSingleStringNotChangedWhenEqualMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(7,'\s','...');
        $this->assertSame('content',$limit->transform('content'));
    }

    function testMultileStringsNotChangedWhenTotalEqualThanMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(16,'\s','...');
        $this->assertSame('content1',$limit->transform('content1'));
        $this->assertSame('content2',$limit->transform('content2'));
    }

    function testMultiByteStringEqualToMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(20,'\s','...');
        $this->assertSame('Iñtërnâtiônàlizætiøn',$limit->transform('Iñtërnâtiônàlizætiøn'));
    }

    function testSingleStringIsBrokenAtRegexDelimiterBeforeMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(17,'\s','...');
        $this->assertSame('The quick fox...',$limit->transform('The quick fox jumps over'));
    }

    function testCanApplyMultipleDelimiters()
    {
        $limit = new T_Filter_LimitedLengthText(17,'\s\.\?','...');
        $this->assertSame('The.quick.fox...',$limit->transform('The.quick.fox.jumps.over'));
    }

    function testStringIsBrokenAtRegexDelimiterWhenRepeatedDelimiters()
    {
        $limit = new T_Filter_LimitedLengthText(17,'\s','...');
        $this->assertSame('The   quick  fox...',$limit->transform('The   quick  fox   jumps   over'));
    }

    function testMultiByteStringIsBrokenAtRegexDelimiterBeforeMaxLength()
    {
        $limit = new T_Filter_LimitedLengthText(17,'\s','...');
        $this->assertSame('Iñtër nâ tiônà...',$limit->transform('Iñtër nâ tiônà liz ætiøn'));
    }

    function testSingleStringIsBrokenAfterDelimiterIfNoBreakBefore()
    {
        $limit = new T_Filter_LimitedLengthText(10,'\s','...');
        $this->assertSame('Thequickfox...',$limit->transform('Thequickfox jumps over'));
    }

    function testSingleStringIsNeverBrokenAtStartWithoutSomeContent()
    {
        $limit = new T_Filter_LimitedLengthText(10,'\s','...');
        $this->assertSame(' Thequickfox...',$limit->transform(' Thequickfox jumps over'));
        $limit = new T_Filter_LimitedLengthText(10,'\s','...');
        $this->assertSame('   Thequickfox...',$limit->transform('   Thequickfox jumps over'));
        $limit = new T_Filter_LimitedLengthText(10,'\s','...');
        $this->assertSame('A...',$limit->transform('A quickfoxjumps over'));
    }

    function testSecondStringIsBrokenAtDelimiterBeforeMax()
    {
        $limit = new T_Filter_LimitedLengthText(32,'\s','...');
        $this->assertSame('The quick fox jumps over',
                          $limit->transform('The quick fox jumps over'));
        $this->assertSame('The...',
                          $limit->transform('The quick fox jumps over'));
    }

    function testMultiByteSecondStringIsBrokenAtDelimiterBeforeMax()
    {
        $limit = new T_Filter_LimitedLengthText(32,'\s','...');
        $this->assertSame('Iñtër nât iônà liz ætiøn',
                          $limit->transform('Iñtër nât iônà liz ætiøn'));
        $this->assertSame('Iñtër...',
                          $limit->transform('Iñtër nât iônà liz ætiøn'));
    }

    function testLimitExceededWithNoBreak()
    {
        $limit = new T_Filter_LimitedLengthText(3,'\s','...');
        $this->assertSame('content...',$limit->transform('content'));
    }

    function testAfterLimitExceededFalseIsReturned()
    {
        $limit = new T_Filter_LimitedLengthText(3,'\s','...');
        $this->assertSame('content...',$limit->transform('content'));
        $this->assertSame(false,$limit->transform('content'));
        $this->assertSame(false,$limit->transform('content'));
    }

    function testNotSplitInTheMiddleOfRepeatedDelimiters()
    {
        $limit = new T_Filter_LimitedLengthText(9,'\s\?','...');
        $this->assertSame('question...',$limit->transform('question? More'));
    }

}