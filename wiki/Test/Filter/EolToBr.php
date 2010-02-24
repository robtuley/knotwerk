<?php
/**
 * Unit test cases for the T_Filter_EolToBr class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_EolToBr unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Filter_EolToBr extends T_Unit_Case
{

    function testNoLineBreaksStringIsUnaffected()
    {
        $f = new T_Filter_EolToBr();
        $this->assertSame('content',$f->transform('content'));
    }

    function testConvertsWindowsLineBreaks()
    {
        $f = new T_Filter_EolToBr();
        $this->assertSame('multi<br />line',$f->transform("multi\r\nline"));
    }

    function testConvertsUnixLineBreaks()
    {
        $f = new T_Filter_EolToBr();
        $this->assertSame('multi<br />line',$f->transform("multi\nline"));
    }

    function testConvertsMultipleMixedLineBreaks()
    {
        $f = new T_Filter_EolToBr();
        $this->assertSame('a<br />b<br />c<br />d',$f->transform("a\nb\r\nc\nd"));
    }

}