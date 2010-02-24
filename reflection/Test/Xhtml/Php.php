<?php
/**
 * Unit test cases for T_Xhtml_Php class.
 *
 * @package reflectionTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Xhtml_Php unit test cases.
 *
 * @package reflectionTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Xhtml_Php extends T_Unit_Case
{

    function testSourceCodeEncapsulatedInOrderedListWithPhpClass()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php $variable=10; ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals($sxe['class'],'php');
        $this->assertEquals($sxe->getName(),'ol');
    }

    function testSourceCodeEncapsulatedWithinCodeTags()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php $variable=10; ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertTrue(count($sxe->xpath('//li/code'))>0);
    }

    function testSourcePhpTagsHighlightedWithTagClass()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php /* comment */ ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(2,count($tags=$sxe->xpath('//span[@class="tag"]')));
        $this->assertEquals(trim($tags[0]),'<?php');
        $this->assertEquals(trim($tags[1]),'?>');
    }

    function testSourcePhpEchoTagsHighlightedWithTagClass()
    {
        if (!ini_get('short_open_tag')) {
            $this->skip('Applicable only with short_open_tag enabled');
        }
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?= /* comment */ ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(2,count($tags=$sxe->xpath('//span[@class="tag"]')));
        $this->assertEquals(trim($tags[0]),'<?=');
        $this->assertEquals(trim($tags[1]),'?>');
    }

    function testSourcePhpInlineCommentHasClassComment()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php // com<ment ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($comment=$sxe->xpath('//span[@class="comment"]')));
        $this->assertEquals(trim($comment[0]),'// com<ment');
    }

    function testMultiLineCommentIsSplitAtUnixLineBreaks()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php /* multi'."\n".' line */ ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(2,count($comment=$sxe->xpath('//span[@class="comment"]')));
        $this->assertEquals(trim($comment[0]),'/* multi');
        $this->assertEquals(trim($comment[1]),'line */');
    }

    function testMultiLineCommentIsSplitAtWindowsLineBreaks()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php /* multi'."\r\n".' line */ ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(2,count($comment=$sxe->xpath('//span[@class="comment"]')));
        $this->assertEquals(trim($comment[0]),'/* multi');
        $this->assertEquals(trim($comment[1]),'line */');
    }

    function testEvenLinesAreGivenTheClassEven()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform("<?php 1;\n2;\n3;\n4; ?>");
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(4,count($lines=$sxe->xpath('//li')));
        foreach ($lines as $i => $li) {
            if ($i%2==1) $this->assertEquals($li['class'],'even');
        }
    }

    function testLineEndingAreTrimmed()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php /* multi       '."\r\n".' line */ ?>');
        $sxe = new SimpleXMLElement($xml);
        $comment=$sxe->xpath('//span[@class="comment"]');
        $this->assertEquals(ltrim($comment[0]),'/* multi');
    }

    function testDocBlockCommentIsMarkedAsComment()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php /**'.EOL.
                                       ' * doc-block'.EOL.
                                       ' */ ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(3,count($comment=$sxe->xpath('//span[@class="comment"]')));
        $this->assertEquals(trim($comment[0]),'/**');
        $this->assertEquals(trim($comment[1]),'* doc-block');
        $this->assertEquals(trim($comment[2]),'*/');
    }

    function testSourceHtmlInlineContentHighlightedAndEscaped()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<h1>Title</h1>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="inline-content"]')));
        $this->assertEquals(trim($content[0]),'<h1>Title</h1>');
    }

    function testMultiLineInlineContent()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('multi'."\n".'line');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(2,count($content=$sxe->xpath('//span[@class="inline-content"]')));
        $this->assertEquals(trim($content[0]),'multi');
        $this->assertEquals(trim($content[1]),'line');
    }

    function testSourcePhpStringHighlightedAndEscaped()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform("<?php 'str<ing' ?>");
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="string"]')));
        $this->assertEquals(trim($content[0]),"'str<ing'");
    }

    function testMultiLineStringIsSplitAtLineBreak()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php "multi'.EOL.'line" ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(2,count($content=$sxe->xpath('//span[@class="string"]')));
        $this->assertEquals(trim($content[0]),'"multi');
        $this->assertEquals(trim($content[1]),'line"');
    }

    function testHighlightEmbeddedVariableInString()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php "embedded $variable in string" ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="variable"]')));
        $this->assertEquals(trim($content[0]),'$variable');
    }

    function testOnlyHighlightTStringInDoubleQuotes()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php "embedded $variable"; myfunc(); ?>');
        $sxe = new SimpleXMLElement($xml);
        $content = '';
        foreach ($sxe->xpath('//span[@class="string"]') as $bit) {
            $content .= trim($bit);
        }
        $this->assertEquals($content,'"embedded"');
    }

    function testOnlyHighlightTStringInDoubleQuotesOverMultiLines()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php "multi $variable '.EOL.
                                      'line $var"; myfunc(); ?>');
        $sxe = new SimpleXMLElement($xml);
        $content = '';
        foreach ($sxe->xpath('//span[@class="string"]') as $bit) {
            $content .= trim($bit);
        }
        $this->assertEquals($content,'"multiline"');
    }

    function testHighlightEmbeddedArrayVariableInString()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php "embedded {$var[\'key\']} string" ?>');
        $sxe = new SimpleXMLElement($xml);
        $content = '';
        foreach ($sxe->xpath('//span[@class="string"]') as $bit) {
            $content .= trim($bit);
        }
        $this->assertEquals($content,'"embedded\'key\'string"');
    }

    function testSourcePhpIntegerHighlighted()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php 100; ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="number"]')));
        $this->assertEquals(trim($content[0]),'100');
    }

    function testSourcePhpDecimalHighlighted()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php 1.234; ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="number"]')));
        $this->assertEquals(trim($content[0]),'1.234');
    }

    // Testing keywords
    // ----------------
    // There are a *lot* of keywords to test, and I'm not going to check all
    // of them. Instead, I'm going to simply check a representative sample to
    // check the keyword code is working as expected.

    function testSourcePhpArrayKeywordHighlighted()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php array(); ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="keyword"]')));
        $this->assertEquals(trim($content[0]),'array');
    }

    function testSourcePhpFunctionKeywordHighlighted()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php function myfunc() { } ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="keyword"]')));
        $this->assertEquals(trim($content[0]),'function');
    }

    function testSourcePhpEchoKeywordHighlighted()
    {
        $src = new T_Xhtml_Php();
        $xml = $src->transform('<?php echo \'string\'; ?>');
        $sxe = new SimpleXMLElement($xml);
        $this->assertEquals(1,count($content=$sxe->xpath('//span[@class="keyword"]')));
        $this->assertEquals(trim($content[0]),'echo');
    }

}
