<?php
class T_Test_Xhtml_Text extends T_Unit_Case
{

    protected function getRootUrl()
    {
        return new T_Url('http','example.com');
    }

    protected function getVisitorRender($node,$header_shift=null)
    {
        $visitor = new T_Xhtml_Text($this->getRootUrl());
        if (!is_null($header_shift)) {
            $visitor->setHeaderAdjustment($header_shift);
        }
        $node->accept($visitor);
        return $visitor->__toString();
    }

    function testFormattedTextNoChildrenRendersContentOnly()
    {
        $wiki = new T_Text_Plain('content');
        $this->assertSame('content',$this->getVisitorRender($wiki));
    }

    function testFormattedTextContentIsEscapedForXhtml()
    {
        $wiki = new T_Text_Plain('a&b');
        $this->assertSame('a&amp;b',$this->getVisitorRender($wiki));
    }

    function testFormattedTextRendersTextChildren()
    {
        $wiki = new T_Text_Plain('parent');
        $wiki->addChild(new T_Text_Plain('child_one'));
        $wiki->addChild(new T_Text_Plain('child_two'));
        $this->assertSame('parentchild_onechild_two',$this->getVisitorRender($wiki));
    }

    function testParagraphRendersInCorrectTags()
    {
        $wiki = new T_Text_Paragraph('content');
        $this->assertSame(EOL.'<p>content</p>',$this->getVisitorRender($wiki));
    }

    function testParagraphTextIsEscapedForXhtml()
    {
        $wiki = new T_Text_Paragraph('a&b');
        $this->assertSame(EOL.'<p>a&amp;b</p>',$this->getVisitorRender($wiki));
    }

    function testParagraphNewlinesAreConvertedToBrTags()
    {
        $wiki = new T_Text_Paragraph("a\n&\r\nb");
        $this->assertSame(EOL.'<p>a<br />&amp;<br />b</p>',$this->getVisitorRender($wiki));
    }

    function testParagraphRendersChildrenWithinCorrectTags()
    {
        $wiki = new T_Text_Paragraph('parent');
        $wiki->addChild(new T_Text_Plain('child_one'));
        $wiki->addChild(new T_Text_Plain('child_two'));
        $this->assertSame(EOL.'<p>parentchild_onechild_two</p>',$this->getVisitorRender($wiki));
    }

    function testCanRenderMultipleParagraphs()
    {
        $wiki = new T_Text_Plain('parent');
        $wiki->addChild(new T_Text_Paragraph('child_one'));
        $wiki->addChild(new T_Text_Paragraph('child_two'));
        $expect = 'parent'.EOL.'<p>child_one</p>'.EOL.'<p>child_two</p>';
        $this->assertSame($expect,$this->getVisitorRender($wiki));
    }

    function testEmphTextRendersInCorrectTags()
    {
        $wiki = new T_Text_Emph('content');
        $this->assertSame('<em>content</em>',$this->getVisitorRender($wiki));
    }

    function testEmphTextIsEscapedForXhtml()
    {
        $wiki = new T_Text_Emph('a&b');
        $this->assertSame('<em>a&amp;b</em>',$this->getVisitorRender($wiki));
    }

    function testEmphTextRendersChildrenWithinCorrectTags()
    {
        $wiki = new T_Text_Emph('parent');
        $wiki->addChild(new T_Text_Plain('child_one'));
        $wiki->addChild(new T_Text_Plain('child_two'));
        $this->assertSame('<em>parentchild_onechild_two</em>',$this->getVisitorRender($wiki));
    }

    function testCanRenderMultipleEmphText()
    {
        $wiki = new T_Text_Plain('parent');
        $wiki->addChild(new T_Text_Emph('child_one'));
        $wiki->addChild(new T_Text_Emph('child_two'));
        $expect = 'parent<em>child_one</em><em>child_two</em>';
        $this->assertSame($expect,$this->getVisitorRender($wiki));
    }

    function testCanRenderHeaderWithCorrectLevelTags()
    {
        $wiki = new T_Text_Header(3,'title');
        $this->assertSame(EOL.'<h3>title</h3>',$this->getVisitorRender($wiki));
    }

    function testHeaderTextIsEscapedForXhtml()
    {
        $wiki = new T_Text_Header(6,'a&b');
        $this->assertSame(EOL.'<h6>a&amp;b</h6>',$this->getVisitorRender($wiki));
    }

    function testSetHeaderAdjustmentFailsWhenLessThanZero()
    {
        $visitor = new T_Xhtml_Text($this->getRootUrl());
        try {
            $visitor->setHeaderAdjustment(-1);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testSetHeaderAdjustmentHasFluentInterface()
    {
        $visitor = new T_Xhtml_Text($this->getRootUrl());
        $test = $visitor->setHeaderAdjustment(3);
        $this->assertSame($visitor,$test);
    }

    function testHeaderAdjustmentAffectsHeaderRenderLevel()
    {
        $wiki = new T_Text_Header(3,'title');
        $this->assertSame(EOL.'<h5>title</h5>',$this->getVisitorRender($wiki,2));
    }

    function testHeaderTagH7OrAboveIsNeverRendered()
    {
        $wiki = new T_Text_Header(3,'title');
        $this->assertSame(EOL.'<p><strong>title</strong></p>',$this->getVisitorRender($wiki,4));
    }

    function testExternalLinkRendersInCorrectTags()
    {
        $wiki = new T_Text_ExternalLink('content','url');
        $this->assertSame('<a class="ext" href="url">content</a>',$this->getVisitorRender($wiki));
    }

    function testExternalLinkTextIsEscapedForXhtml()
    {
        $wiki = new T_Text_ExternalLink('con&tent','url');
        $this->assertSame('<a class="ext" href="url">con&amp;tent</a>',$this->getVisitorRender($wiki));
    }

    function testExternalLinkUrlIsEscapedForXhtml()
    {
        $wiki = new T_Text_ExternalLink('content','u&rl');
        $this->assertSame('<a class="ext" href="u&amp;rl">content</a>',$this->getVisitorRender($wiki));
    }

    function testExternalLinkClassCanBeChanged()
    {
        $wiki = new T_Text_ExternalLink('content','url');
        $visitor = new T_Xhtml_Text($this->getRootUrl());
        $visitor->setExternalLinkClass('new-class');
        $wiki->accept($visitor);
        $test = $visitor->__toString();;
        $this->assertSame('<a class="new-class" href="url">content</a>',$test);
    }

    function testExternalLinkRendersChildrenWithinCorrectTags()
    {
        $wiki = new T_Text_ExternalLink('parent','url');
        $wiki->addChild(new T_Text_Plain('child_one'));
        $wiki->addChild(new T_Text_Plain('child_two'));
        $this->assertSame('<a class="ext" href="url">parentchild_onechild_two</a>',
                          $this->getVisitorRender($wiki)  );
    }

    function testCanRenderMultipleExternalLinks()
    {
        $wiki = new T_Text_Plain('parent');
        $wiki->addChild(new T_Text_ExternalLink('content1','url1'));
        $wiki->addChild(new T_Text_ExternalLink('content2','url2'));
        $expect = 'parent'.
                  '<a class="ext" href="url1">content1</a>'.
                  '<a class="ext" href="url2">content2</a>';
        $this->assertSame($expect,$this->getVisitorRender($wiki));
    }

    function testInternalLinkRendersInCorrectTags()
    {
        $wiki = new T_Text_InternalLink('content','/some/path');
        $url = $this->getRootUrl()->__toString().'/some/path';
        $this->assertSame('<a href="'.$url.'">content</a>',$this->getVisitorRender($wiki));
    }

    function testInternalLinkTextIsEscapedForXhtml()
    {
        $wiki = new T_Text_InternalLink('con&tent','/some/path');
        $url = $this->getRootUrl()->__toString().'/some/path';
        $this->assertSame('<a href="'.$url.'">con&amp;tent</a>',$this->getVisitorRender($wiki));
    }

    function testInternalLinkUrlIsEscapedForXhtml()
    {
        $wiki = new T_Text_InternalLink('content','/so&me/pa&th');
        $url = $this->getRootUrl()->__toString().'/so&amp;me/pa&amp;th';
        $this->assertSame('<a href="'.$url.'">content</a>',$this->getVisitorRender($wiki));
    }

    function testInternalLinkRendersChildrenWithinCorrectTags()
    {
        $wiki = new T_Text_InternalLink('parent','/url');
        $url = $this->getRootUrl()->__toString().'/url';
        $wiki->addChild(new T_Text_Plain('child_one'));
        $wiki->addChild(new T_Text_Plain('child_two'));
        $url = $this->getRootUrl()->__toString().'/url';
        $this->assertSame('<a href="'.$url.'">parentchild_onechild_two</a>',
                          $this->getVisitorRender($wiki)  );
    }

    function testCanRenderMultipleInternalLinks()
    {
        $wiki = new T_Text_Plain('parent');
        $wiki->addChild(new T_Text_InternalLink('content1','/url1'));
        $wiki->addChild(new T_Text_InternalLink('content2','/url2'));
        $url1 = $this->getRootUrl()->__toString().'/url1';
        $url2 = $this->getRootUrl()->__toString().'/url2';

        $expect = 'parent'.
                  '<a href="'.$url1.'">content1</a>'.
                  '<a href="'.$url2.'">content2</a>';
        $this->assertSame($expect,$this->getVisitorRender($wiki));
    }

}
