<?php
/**
 * Unit test cases for the T_Text_ParagraphLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_ParagraphLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_ParagraphLexer extends T_Unit_Case
{

    function testPlacesSingleSectionOfTextInSingleParagraph()
    {
        $element = new T_Text_Plain('content');
        $element->accept(new T_Text_ParagraphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('content'));
        $this->assertEquals($expected,$element);
    }

    function testContentIsTrimmedIntoParagraph()
    {
        $element = new T_Text_Plain(EOL.EOL.' content  '.EOL.EOL.EOL);
        $element->accept(new T_Text_ParagraphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('content'));
        $this->assertEquals($expected,$element);
    }

    function testMultipleParagraphsAreRecognisedByTwoLineBreaks()
    {
        $content = array("para1\n\npara2",
                         "para1\r\n\r\npara2",
                         "para1\r\n\npara2",
                         "para1\n\x0bpara2",
                         "para1\x85\x0bpara2",
                         "para1\r\rpara2",
                         "para1\r\n\rpara2",
                         "para1\r\x85para2",
                         "para1\n\rpara2" );
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('para1'))
                 ->addChild(new T_Text_Paragraph('para2'));
        foreach ($content as $str) {
            $element = new T_Text_Plain($str);
            $element->accept(new T_Text_ParagraphLexer());
            $this->assertEquals($expected,$element,"failed on $str");
        }
    }

    function testASingleParagraphCanContainSingleLineBreaks()
    {
        $element = new T_Text_Plain("multi\nline\r\ncontent");
        $element->accept(new T_Text_ParagraphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph("multi\nline\r\ncontent"));
        $this->assertEquals($expected,$element);
    }

    function testEmptyParagraphsAreRemoved()
    {
        $content = "\n\n   \n\npara1\n\n \n\n   \t \n\npara2\n\n  \n\n";
        $element = new T_Text_Plain($content);
        $element->accept(new T_Text_ParagraphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('para1'))
                 ->addChild(new T_Text_Paragraph('para2'));
        $this->assertEquals($expected,$element);
    }

    function testParagraphsCanBeSeparatedByMoreThanTwoLineBreaks()
    {
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('para1'))
                 ->addChild(new T_Text_Paragraph('para2'));
        $element = new T_Text_Plain("para1\n\n\n\npara2");
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertEquals($expected,$element);
    }

    function testMultiByteTextParagraphs()
    {
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('Iñtër'))
                 ->addChild(new T_Text_Paragraph('nâtiônàliz'))
                 ->addChild(new T_Text_Paragraph('ætiøn'));
        $element = new T_Text_Plain("Iñtër\n\nnâtiônàliz\n\nætiøn");
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertEquals($expected,$element);
    }

    function testParagraphsCanBeSeparatedByMoreLineBreaksSeparatedByWhitespace()
    {
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Paragraph('para1'))
                 ->addChild(new T_Text_Paragraph('para2'));
        $element = new T_Text_Plain("para1\n \t \npara2");
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertEquals($expected,$element);
    }

    function testThatContentInsideParagraphClassRemainsUnchanged()
    {
        $element = new T_Text_Paragraph("no\n\nchange");
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertSame("no\n\nchange",$element->getContent());
    }

    function testParagraphsParsedInChildElement()
    {
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain(),'nest');
        $expected->addChild(new T_Text_Paragraph('para1'))
                 ->addChild(new T_Text_Paragraph('para2'));
        $expected->nest->addChild(new T_Text_Paragraph('nested1'))
                       ->addChild(new T_Text_Paragraph('nested2'));
        $element = new T_Text_Plain("para1\n\n\npara2");
        $element->addChild(new T_Text_Plain("nested1\n\nnested2"),'nest');
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertEquals($expected,$element);
    }

    function testThatChildrenOfParagraphNotParsed()
    {
        $element = new T_Text_Paragraph("para1\n\n\npara2");
        $element->addChild(new T_Text_Plain("nested1\n\nnested2"),'nest');
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertSame("nested1\n\nnested2",$element->nest->getContent());
    }

    function testThatHeadersRemainUnaffected()
    {
        $element = new T_Text_Header(2,"title");
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertEquals(new T_Text_Header(2,"title"),$element);
    }

    function testThatHeaderChildrenRemainUnaffected()
    {
        $element = new T_Text_Header(2,"title");
        $element->addChild(new T_Text_Plain('some text'));
        $expect = new T_Text_Header(2,"title");
        $expect->addChild(new T_Text_Plain('some text'));
        $element->accept(new T_Text_ParagraphLexer());
        $this->assertEquals($expect,$element);
    }

}
