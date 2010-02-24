<?php
/**
 * Unit test cases for the T_Text_DividerLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_DividerLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_DividerLexer extends T_Unit_Case
{

    function testPlacesSingleDividerInSingleObject()
    {
        $element = new T_Text_Plain('----');
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Divider());
        $this->assertEquals($expected,$element);
    }

    function testWhitespaceIsIgnored()
    {
        $element = new T_Text_Plain(" \t  ----  \t ");
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Divider());
        $this->assertEquals($expected,$element);
    }

    function testExtraDashesAreAcceptedAsPartOfDivider()
    {
        $element = new T_Text_Plain(' ------------- ');
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Divider());
        $this->assertEquals($expected,$element);
    }

    function testWithPriorContent()
    {
        $element = new T_Text_Plain("Prior \r\n-----");
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_Divider());
        $this->assertEquals($expected,$element);
    }

    function testWithPostContent()
    {
        $element = new T_Text_Plain("-----\n Post");
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testWithPriorAndPostContent()
    {
        $element = new T_Text_Plain("Prior\n--------\r\nPost");
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior'));
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain('Post'));
        $this->assertEquals($expected,$element);
    }

    function testWithMiddleContent()
    {
        $element = new T_Text_Plain("----\nmiddle\n----");
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain('middle'));
        $expected->addChild(new T_Text_Divider());
        $this->assertEquals($expected,$element);
    }

    function testWithPriorPostAndMiddleContent()
    {
        $text = "Prior\n------\r\nmiddle\n ------ \nPost";
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior'));
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain('middle'));
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain('Post'));
        $this->assertEquals($expected,$element);
    }

    function testMultiByteTextWithPriorPostAndMiddleContent()
    {
        $text = "Iñtër\n----\nàlizætiøn\n----\nnâtiônàlizætiøn";
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Iñtër'));
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain('àlizætiøn'));
        $expected->addChild(new T_Text_Divider());
        $expected->addChild(new T_Text_Plain('nâtiônàlizætiøn'));
        $this->assertEquals($expected,$element);
    }

    function testAlternativeUsesOfDashesRemainsUnaffected()
    {
        $unaffected = array("---",
                            "----- ----- -----",
                            "---- some text"  );
        foreach ($unaffected as $str) {
            $element = new T_Text_Plain($str);
            $element->accept(new T_Text_DividerLexer());
            $expected = new T_Text_Plain($str);
            $this->assertEquals($expected,$element,"failed on $str");
        }
    }

    function testParsesAQuote()
    {
        $element = new T_Text_Quote('cite','----');
        $element->accept(new T_Text_DividerLexer());
        $expected = new T_Text_Quote('cite',null);
        $expected->addChild(new T_Text_Divider());
        $this->assertEquals($expected,$element);
    }

}