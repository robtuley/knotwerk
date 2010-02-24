<?php
/**
 * Unit test cases for the T_Text_HeaderLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_HeaderLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_HeaderLexer extends T_Unit_Case
{

    function testFormattedTextUnaffectedWhenNoHeader()
    {
        $element = new T_Text_Plain('noheader');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain('noheader');
        $this->assertEquals($expected,$element);
    }

    function testFormattedTextWhichContainsSingleHeaderInMiddle()
    {
        $element = new T_Text_Plain('pre'.EOL.'== header =='.EOL.'post');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('pre'));
        $expected->addChild(new T_Text_Header(1,'header'));
        $expected->addChild(new T_Text_Plain('post'));
        $this->assertEquals($expected,$element);
    }

    function testFormattedTextWhichContainsSingleHeaderAtStart()
    {
        $element = new T_Text_Plain('== header =='.EOL.'post');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Header(1,'header'));
        $expected->addChild(new T_Text_Plain('post'));
        $this->assertEquals($expected,$element);
    }

    function testFormattedTextWhichContainsSingleHeaderAtEnd()
    {
        $element = new T_Text_Plain('pre'.EOL.'== header ==');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('pre'));
        $expected->addChild(new T_Text_Header(1,'header'));
        $this->assertEquals($expected,$element);
    }

    function testFormattedTextWhichContainsSingleHeaderOnly()
    {
        $element = new T_Text_Plain('== header ==');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Header(1,'header'));
        $this->assertEquals($expected,$element);
    }

    function testFormattedTextWhichContainsMultipleHeaderInMiddle()
    {
        $element = new T_Text_Plain('pre'.EOL.'== header1 =='.EOL.'middle'.EOL.'==== header2 ===='.EOL.'post');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('pre'));
        $expected->addChild(new T_Text_Header(1,'header1'));
        $expected->addChild(new T_Text_Plain('middle'));
        $expected->addChild(new T_Text_Header(3,'header2'));
        $expected->addChild(new T_Text_Plain('post'));
        $this->assertEquals($expected,$element);
    }

    function testHandlesUnbalancedEqualsSigns()
    {
        $element = new T_Text_Plain('==== header ==');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Header(1,'== header'));
        $this->assertEquals($expected,$element);
    }

    function testHeaderLineCanBePrefixedAndSuffixedByWhitespace()
    {
        $element = new T_Text_Plain('pre'.EOL.'    == header ==        '.EOL.'post');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('pre'));
        $expected->addChild(new T_Text_Header(1,'header'));
        $expected->addChild(new T_Text_Plain('post'));
        $this->assertEquals($expected,$element);
    }

    function testHeaderWithSingleEqualsSignsAreNotRecognised()
    {
        $element = new T_Text_Plain('= noheader =');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain('= noheader =');
        $this->assertEquals($expected,$element);
    }

    function testMaxHeaderLevelIsSix()
    {
        $element = new T_Text_Plain('========= header =========');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Header(6,'== header =='));
        $this->assertEquals($expected,$element);
    }

    function testFormattedTextCanContainInternationalCharacters()
    {
        $element = new T_Text_Plain('Iñtërnâ'.EOL.'== Iñtërnâtiônàlizætiøn =='.EOL.'âtiônàlizætiø'.EOL.'==== ërnâtiônàlizætiøn ===='.EOL.'ñtërnâtiônàlizæ');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Iñtërnâ'));
        $expected->addChild(new T_Text_Header(1,'Iñtërnâtiônàlizætiøn'));
        $expected->addChild(new T_Text_Plain('âtiônàlizætiø'));
        $expected->addChild(new T_Text_Header(3,'ërnâtiônàlizætiøn'));
        $expected->addChild(new T_Text_Plain('ñtërnâtiônàlizæ'));
        $this->assertEquals($expected,$element);
    }

    function testExtraLineFeedsAreIgnored()
    {
        $element = new T_Text_Plain('pre'.EOL.'   '.EOL.EOL.'   == header =='.EOL.EOL.'  '.EOL.'post');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('pre'));
        $expected->addChild(new T_Text_Header(1,'header'));
        $expected->addChild(new T_Text_Plain('post'));
        $this->assertEquals($expected,$element);
    }

    function testWindowsLineFeedIsRecognised()
    {
        $element = new T_Text_Plain('pre'."\r\n".'== header =='."\r\n".'post');
        $element->accept(new T_Text_HeaderLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('pre'));
        $expected->addChild(new T_Text_Header(1,'header'));
        $expected->addChild(new T_Text_Plain('post'));
        $this->assertEquals($expected,$element);
    }

}