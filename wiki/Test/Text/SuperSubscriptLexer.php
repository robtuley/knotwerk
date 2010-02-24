<?php
/**
 * Unit test cases for the T_Text_SuperSubscriptLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_SuperSubscriptLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_SuperSubscriptLexer extends T_Unit_Case
{

    function testMatchesSingleCharSuperscript()
    {
        $element = new T_Text_Plain('^2');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testMatchesMultiCharSuperscript()
    {
        $element = new T_Text_Plain('^{st}');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Superscript('st'));
        $this->assertEquals($expected,$element);
    }

    function testMatchesSingleCharSubscript()
    {
        $element = new T_Text_Plain('_2');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Subscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testMatchesMultiCharSubscript()
    {
        $element = new T_Text_Plain('_{st}');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Subscript('st'));
        $this->assertEquals($expected,$element);
    }

    function testMatchesSingleMultiByteCharSuperscript()
    {
        $element = new T_Text_Plain('^ñ');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Superscript('ñ'));
        $this->assertEquals($expected,$element);
    }

    function testMatchesMultiCharMultiByteSuperscript()
    {
        $element = new T_Text_Plain('^{Iñtërnâtiônàlizætiøn}');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Superscript('Iñtërnâtiônàlizætiøn'));
        $this->assertEquals($expected,$element);
    }

    function testNoMatchesForHatFollowedBySpace()
    {
        $element = new T_Text_Plain('^ ');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain('^ ');
        $this->assertEquals($expected,$element);
    }

    function testDoesNotAcceptNestedSubscripts()
    {
        $element = new T_Text_Plain('^{m^2}');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('^{m'));
        $expected->addChild(new T_Text_Superscript('2'));
        $expected->addChild(new T_Text_Plain('}'));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorContent()
    {
        $element = new T_Text_Plain('Area is m^2');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Area is m'));
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testWithPostContent()
    {
        $element = new T_Text_Plain('^2 Post');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Superscript('2'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorAndPostContent()
    {
        $element = new T_Text_Plain('Area is 20m^2 in total');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Area is 20m'));
        $expected->addChild(new T_Text_Superscript('2'));
        $expected->addChild(new T_Text_Plain(' in total'));
        $this->assertEquals($expected,$element);
    }

    function testWithMiddleContent()
    {
        $element = new T_Text_Plain('^2 middle _3');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Superscript('2'));
        $expected->addChild(new T_Text_Plain(' middle '));
        $expected->addChild(new T_Text_Subscript('3'));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorPostAndMiddleContent()
    {
        $text = '20cm^3 of H_2O';
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('20cm'));
        $expected->addChild(new T_Text_Superscript('3'));
        $expected->addChild(new T_Text_Plain(' of H'));
        $expected->addChild(new T_Text_Subscript('2'));
        $expected->addChild(new T_Text_Plain('O'));
        $this->assertEquals($expected,$element);
    }

    function testMultiByteTextWithPriorPostAndMiddleContent()
    {
        $text = 'Iñtër^{nâtiôn}àlizætiøn_{Iñtër}nâtiônàlizætiøn';
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Iñtër'));
        $expected->addChild(new T_Text_Superscript('nâtiôn'));
        $expected->addChild(new T_Text_Plain('àlizætiøn'));
        $expected->addChild(new T_Text_Subscript('Iñtër'));
        $expected->addChild(new T_Text_Plain('nâtiônàlizætiøn'));
        $this->assertEquals($expected,$element);
    }

    function testAlternativeUsesOfHatsAndUnderscoreRemainUnaffected()
    {
        $unaffected = array("Followed by space ^ ",
                            "Followed by nothing ^",
                            "Followed by space _ ",
                            "Followed by nothing _",  );
        foreach ($unaffected as $str) {
            $element = new T_Text_Plain($str);
            $element->accept(new T_Text_SuperSubscriptLexer());
            $expected = new T_Text_Plain($str);
            $this->assertEquals($expected,$element,"failed on '$str'");
        }
    }

    function testParsesAParagraph()
    {
        $element = new T_Text_Paragraph('^2');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Paragraph();
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testParsesAHeader()
    {
        $element = new T_Text_Header(4,'^2');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Header(4,null);
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testParsesAnInternalLink()
    {
        $element = new T_Text_InternalLink('^2','url');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_InternalLink(null,'url');
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testParsesAnExternalLink()
    {
        $element = new T_Text_ExternalLink('^2','url');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_ExternalLink(null,'url');
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

    function testParsesAnEmphText()
    {
        $element = new T_Text_Emph('^2');
        $element->accept(new T_Text_SuperSubscriptLexer());
        $expected = new T_Text_Emph(null);
        $expected->addChild(new T_Text_Superscript('2'));
        $this->assertEquals($expected,$element);
    }

}