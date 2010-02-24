<?php
/**
 * Unit test cases for the T_Text_LinkLexerLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_LinkLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_LinkLexer extends T_Unit_Case
{

    function testPlacesSingleExternalLinkInSingleObject()
    {
        $element = new T_Text_Plain('[http://example.com content]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $this->assertEquals($expected,$element);
    }

    function testPlacesSingleInternalLinkInSingleObject()
    {
        $element = new T_Text_Plain('[/some/path.ext content]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_InternalLink('content','/some/path.ext'));
        $this->assertEquals($expected,$element);
    }


    function testPlacesWhitespaceIsPreservedInContent()
    {
        $element = new T_Text_Plain('[/some/path.ext    content   ]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_InternalLink('   content   ','/some/path.ext'));
        $this->assertEquals($expected,$element);
    }

    function testExternalUrlTypesThatAreAccepted()
    {
        $url = array("http://example.com",
                     "https://example.com",
                     "ftp://example.com",
                     "mailto:rob@example.com",
                     "http://example.com/file.php",
                     "http://example.com/file.php?name=value",
                     "http://example.com/file.php?name1=value1&name2=value2",
                     "http://example.com/file.php?name=encoded%20value"  );
        foreach ($url as $str) {
            $element = new T_Text_Plain("[$str content]");
            $element->accept(new T_Text_LinkLexer());
            $expected = new T_Text_Plain();
            $expected->addChild(new T_Text_ExternalLink('content',$str));
            $this->assertEquals($expected,$element,"failed on $str");
        }
    }

    function testLinkWithPriorContent()
    {
        $element = new T_Text_Plain('Prior [http://example.com content]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $this->assertEquals($expected,$element);
    }

    function testLinkWithPostContent()
    {
        $element = new T_Text_Plain('[http://example.com content] Post');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testLinkWithPriorAndPostContent()
    {
        $element = new T_Text_Plain('Prior [http://example.com content] Post');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testLinkWithMiddleContent()
    {
        $element = new T_Text_Plain('[http://aa.com bb] middle [http://cc.com dd]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_ExternalLink('bb','http://aa.com'));
        $expected->addChild(new T_Text_Plain(' middle '));
        $expected->addChild(new T_Text_ExternalLink('dd','http://cc.com'));
        $this->assertEquals($expected,$element);
    }

    function testLinkWithPriorPostAndMiddleContent()
    {
        $text = 'Prior [http://aa.com bb] middle [http://cc.com dd] Post';
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Prior '));
        $expected->addChild(new T_Text_ExternalLink('bb','http://aa.com'));
        $expected->addChild(new T_Text_Plain(' middle '));
        $expected->addChild(new T_Text_ExternalLink('dd','http://cc.com'));
        $expected->addChild(new T_Text_Plain(' Post'));
        $this->assertEquals($expected,$element);
    }

    function testMultiByteTextWithPriorPostAndMiddleContent()
    {
        $text = 'Iñtër[http://aa.com nâtiôn]àlizætiøn[http://cc.com Iñtër]nâtiônàlizætiøn';
        $element = new T_Text_Plain($text);
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Plain();
        $expected->addChild(new T_Text_Plain('Iñtër'));
        $expected->addChild(new T_Text_ExternalLink('nâtiôn','http://aa.com'));
        $expected->addChild(new T_Text_Plain('àlizætiøn'));
        $expected->addChild(new T_Text_ExternalLink('Iñtër','http://cc.com'));
        $expected->addChild(new T_Text_Plain('nâtiônàlizætiøn'));
        $this->assertEquals($expected,$element);
    }

    function testAlternativeUsesOfSquareBracketsUnaffected()
    {
        $unaffected = array("Some [] brackets",
                            "some [brackets]",
                            "some [more http://example.com] more",
                            "A single [http://example.com no closing",
                            "No opening ] bracket"  );
        foreach ($unaffected as $str) {
            $element = new T_Text_Plain($str);
            $element->accept(new T_Text_LinkLexer());
            $expected = new T_Text_Plain($str);
            $this->assertEquals($expected,$element,"failed on $str");
        }
    }

    function testParsesAParagraph()
    {
        $element = new T_Text_Paragraph('[http://example.com content]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Paragraph();
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $this->assertEquals($expected,$element);
    }

    function testParsesAHeader()
    {
        $element = new T_Text_Header(4,'[http://example.com content]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Header(4,null);
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $this->assertEquals($expected,$element);
    }

    function testParsesEmphasisedText()
    {
        $element = new T_Text_Emph('[http://example.com content]');
        $element->accept(new T_Text_LinkLexer());
        $expected = new T_Text_Emph();
        $expected->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $this->assertEquals($expected,$element);
    }

    function testNoRecursiveLinkParsing()
    {
        $internal = new T_Text_InternalLink('[http://example.com content]','url');
        $i_expected = clone($internal);
        $external = new T_Text_ExternalLink('[http://example.com content]','url');
        $x_expected = clone($external);
        $internal->accept(new T_Text_LinkLexer());
        $external->accept(new T_Text_LinkLexer());
        $this->assertEquals($i_expected,$internal);
        $this->assertEquals($x_expected,$external);
    }

    function testLinksParsedInChildElement()
    {
        $expected = new T_Text_Plain('parent');
        $expected->addChild(new T_Text_Plain(),'nest');
        $expected->nest->addChild(new T_Text_ExternalLink('content','http://example.com'));
        $element = new T_Text_Plain('parent');
        $element->addChild(new T_Text_Plain('[http://example.com content]'),'nest');
        $element->accept(new T_Text_LinkLexer());
        $this->assertEquals($expected,$element);
    }

}
