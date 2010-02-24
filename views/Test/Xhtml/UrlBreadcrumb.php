<?php
class T_Test_Xhtml_UrlBreadcrumb extends T_Unit_Case
{

    protected $indent = '    ';
    protected $visitor;

    function setUp()
    {
        $this->visitor = new T_Xhtml_UrlBreadcrumb();
    }

    function testSingleCompositeUrlNotActive()
    {
        $url = new T_Url_Collection('title','foo','example.com');

        ob_start();
        $url->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $this->assertSame($xhtml,'');
    }

    function testSingleUrlIsActive()
    {
        $url = new T_Url_Leaf('title','test','example.com');
        $url->setActive();

        ob_start();
        $url->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<ul>'.EOL.
                   $this->indent.'<li> / <a href="test://example.com">title</a>'.EOL.
                   $this->indent.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testCanSetSeparator()
    {
        $this->visitor = new T_Xhtml_UrlBreadcrumb('---');
        $url = new T_Url_Leaf('title','test','example.com');
        $url->setActive();

        ob_start();
        $url->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<ul>'.EOL.
                   $this->indent.'<li>---<a href="test://example.com">title</a>'.EOL.
                   $this->indent.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testUrlCompositeWith1SubLayerNoActive()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Collection('child2','test','c2.com');

        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $this->assertSame($xhtml,'');
    }

    function testUrlCompositeWith1SubLayerFirstActive()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Collection('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');

        $parent->setActive();
        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<ul>'.EOL.
                   $this->indent.'<li> / <a href="test://p.com">parent</a>'.EOL.
                   $this->indent.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testUrlCompositeWith1SubLayerLastActive()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Collection('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');

        $child2->setActive();
        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<ul>'.EOL.
                   $this->indent.'<li> / <a href="test://p.com">parent</a>'.EOL.
                   $this->indent.'<ul>'.EOL.
                   $this->indent.$this->indent.
                                    '<li> / <a href="test://c2.com">child2</a>'.EOL.
                   $this->indent.$this->indent.'</li>'.EOL.
                   $this->indent.'</ul>'.EOL.
                   $this->indent.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testUrlCompositeWith2SubLayerMidActive()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Collection('child2','test','c2.com');
        $childchild = new T_Url_Collection('childchild','test','cc.com');


        $child2->addChild($childchild);
        $child2->setActive();
        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<ul>'.EOL.
                   $this->indent.'<li> / <a href="test://p.com">parent</a>'.EOL.
                   $this->indent.'<ul>'.EOL.
                   $this->indent.$this->indent.
                                    '<li> / <a href="test://c2.com">child2</a>'.EOL.
                   $this->indent.$this->indent.'</li>'.EOL.
                   $this->indent.'</ul>'.EOL.
                   $this->indent.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testMultiActiveAtSingleLevelException()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Collection('child2','test','c2.com');

        $child2->setActive();
        $child1->setActive();
        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();
        try {
            $parent->accept($this->visitor);
            $this->fail();
        } catch(OutOfRangeException $e) {}
        ob_end_clean();
    }

}
