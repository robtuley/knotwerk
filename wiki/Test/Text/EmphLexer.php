<?php
/**
 * Unit test cases for the T_Text_EmphLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_EmphLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_EmphLexer extends T_Unit_Case
{

    function testPlacesSingleEmphasisedTextInSingleObject()
    {
        $element = new T_Text_Plain('**content**');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Emph('content'));
        $this->assertEquals($expected,$element);
    }

    function testWhitespaceIsPreservedInContent()
    {
        $element = new T_Text_Plain('**    content   **');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Emph('    content   '));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorContent()
    {
        $element = new T_Text_Plain('Prior **strong**');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_Emph('strong'));
        $this->assertEquals($expected,$element);
    }

    function testWithPostContent()
    {
        $element = new T_Text_Plain('**strong** Post');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Emph('strong'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorAndPostContent()
    {
        $element = new T_Text_Plain('Prior **strong** Post');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_Emph('strong'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testWithMiddleContent()
    {
        $element = new T_Text_Plain('**aa** middle **bb**');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Emph('aa'));
        $expected->addChild(new T_Text_Plain(' middle '));
        $expected->addChild(new T_Text_Emph('bb'));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorPostAndMiddleContent()
    {
        $text = 'Prior **aa** middle **bb** Post';
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_Emph('aa'));
        $expected->addChild(new T_Text_Plain(' middle '));
        $expected->addChild(new T_Text_Emph('bb'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testMultiByteTextWithPriorPostAndMiddleContent()
    {
        $text = 'Iñtër**nâtiôn**àlizætiøn**Iñtër**nâtiônàlizætiøn';
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Iñtër'));
        $expected->addChild(new T_Text_Emph('nâtiôn'));
        $expected->addChild(new T_Text_Plain('àlizætiøn'));
        $expected->addChild(new T_Text_Emph('Iñtër'));
        $expected->addChild(new T_Text_Plain('nâtiônàlizætiøn'));
        $this->assertEquals($expected,$element);
    }

    function testAlternativeUsesOfAsterisksRemainUnaffected()
    {
        $unaffected = array("Some *single* asterisks",
                            "some **stars*",
                            "some **** stars with no content",
                            "A single ** double star"  );
        foreach ($unaffected as $str) {
            $element = new T_Text_Plain($str);
            $element->accept(new T_Text_EmphLexer());
            $expected = new T_Text_Plain($str);
            $this->assertEquals($expected,$element,"failed on $str");
        }
    }

    function testParsesAParagraph()
    {
        $element = new T_Text_Paragraph('**content**');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Paragraph();
        $expected->addChild(new T_Text_Emph('content'));
        $this->assertEquals($expected,$element);
    }

    function testDoesNotParseAHeader()
    {
        $element = new T_Text_Header(4,'**content**');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_Header(4,'**content**');
        $this->assertEquals($expected,$element);
    }

    function testParsesAnInternalLink()
    {
        $element = new T_Text_InternalLink('**content**','url');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_InternalLink(null,'url');
        $expected->addChild(new T_Text_Emph('content'));
        $this->assertEquals($expected,$element);
    }

    function testParsesAnExternalLink()
    {
        $element = new T_Text_ExternalLink('**content**','url');
        $element->accept(new T_Text_EmphLexer());
        $expected = new T_Text_ExternalLink(null,'url');
        $expected->addChild(new T_Text_Emph('content'));
        $this->assertEquals($expected,$element);
    }

    function testNoRecursiveEmphTextParsing()
    {
        $strong = new T_Text_Emph('**content**');
        $expected = clone($strong);
        $strong->accept(new T_Text_EmphLexer());
        $this->assertEquals($expected,$strong);
    }

    function testEmphTextParsedInChildElement()
    {
        $expected = new T_Text_Plain('parent');
        $expected->addChild(new T_Text_Plain(),'nest');
        $expected->nest->addChild(new T_Text_Emph('content'));
        $element = new T_Text_Plain('parent');
        $element->addChild(new T_Text_Plain('**content**'),'nest');
        $element->accept(new T_Text_EmphLexer());
        $this->assertEquals($expected,$element);
    }

}
