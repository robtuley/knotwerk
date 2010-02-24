<?php
class T_Test_Xhtml_TextPreview extends T_Unit_Case
{

    protected function getRootUrl()
    {
        return new T_Url('http','example.com');
    }

    protected function getVisitorRender($node,$visitor)
    {
        $node->accept($visitor);
        return $visitor->__toString();
    }

    function testNotAffectWhenUnderLimitWithAllTypes()
    {
        $wiki = new T_Text_Plain('parent');
        $wiki->addChild(new T_Text_Paragraph('child1'));
        $wiki->addChild(new T_Text_Header(3,'child2'));
        $wiki->addChild(new T_Text_InternalLink('child3','/some/path'));
        $wiki->addChild(new T_Text_InternalLink('child4','url'));
        $wiki->addChild(new T_Text_Emph('child5'));
        $test = new T_Xhtml_TextPreview(1000,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($wiki,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsFormattedText()
    {
        $wiki = new T_Text_Plain('some content');
        $short = new T_Text_Plain('some ...');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsDelimiterIncludesPunctuation()
    {
        $wiki = new T_Text_Plain('some.?!:;, content');
        $short = new T_Text_Plain('some ...');
        $test = new T_Xhtml_TextPreview(15,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsChildren()
    {
        $wiki = new T_Text_Plain('some content');
        $wiki->addChild(new T_Text_Plain('more content'));
        $short = new T_Text_Plain('some content');
        $short->addChild(new T_Text_Plain('more ...'));
        $test = new T_Xhtml_TextPreview(20,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testHandlesParagraphThatModifiesOriginalFilter()
    {
        $wiki = new T_Text_Paragraph('some content');
        $wiki->addChild(new T_Text_Plain('more content'));
        $short = new T_Text_Paragraph('some content');
        $short->addChild(new T_Text_Plain('more ...'));
        $test = new T_Xhtml_TextPreview(20,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testDoesNotRenderChildrenOnceLimitIsReached()
    {
        $wiki = new T_Text_Plain('some content');
        $wiki->addChild(new T_Text_Paragraph('child1'));
        $wiki->addChild(new T_Text_Header(3,'child2'));
        $wiki->addChild(new T_Text_InternalLink('child3','/some/path'));
        $wiki->addChild(new T_Text_InternalLink('child4','url'));
        $short = new T_Text_Plain('some ...');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsParagraph()
    {
        $wiki = new T_Text_Paragraph('some content');
        $short = new T_Text_Paragraph('some ...');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsHeader()
    {
        $wiki = new T_Text_Header(2,'some content');
        $short = new T_Text_Header(2,'some ...');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsLinkToInternal()
    {
        $wiki = new T_Text_InternalLink('some content','/some/path');
        $short = new T_Text_InternalLink('some ...','/some/path');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsLinkToExternal()
    {
        $wiki = new T_Text_ExternalLink('some content','url');
        $short = new T_Text_ExternalLink('some ...','url');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

    function testLimitsEmphText()
    {
        $wiki = new T_Text_Emph('some content');
        $short = new T_Text_Emph('some ...');
        $test = new T_Xhtml_TextPreview(10,$this->getRootUrl());
        $ref = new T_Xhtml_Text($this->getRootUrl());
        $this->assertSame($this->getVisitorRender($short,$ref),
                          $this->getVisitorRender($wiki,$test)  );
    }

}
