<?php
class T_Test_Xhtml_UrlList extends T_Test_Xhtml_List
{

    protected $indent = '    ';

    function setUp()
    {
        $this->visitor = new T_Xhtml_UrlList();
    }

    function testVisitUrlCompositeWithOneSubTreeLayer()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');

        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<a class="parent" href="test://p.com">parent</a>'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li><a href="test://c1.com">child1</a></li>'.EOL.
                   $this->indent.'<li><a href="test://c2.com">child2</a></li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testVisitUrlCompositeWithActiveParent()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');

        $parent->setActive();
        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<a class="cur parent" href="test://p.com">parent</a>'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li><a href="test://c1.com">child1</a></li>'.EOL.
                   $this->indent.'<li><a href="test://c2.com">child2</a></li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testVisitUrlCompositeWithActiveChild()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Leaf('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');

        $child2->setActive();
        $parent->addChild($child1);
        $parent->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $c2_link = '<a class="cur" href="test://c2.com">child2</a>';
        $expected = EOL.        // build expected output
                   '<a class="cur parent" href="test://p.com">parent</a>'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li><a href="test://c1.com">child1</a></li>'.EOL.
                   $this->indent.'<li class="cur">'.$c2_link.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

    function testVisitUrlCompositeWithNestedChild()
    {
        $parent = new T_Url_Collection('parent','test','p.com');
        $child1 = new T_Url_Collection('child1','test','c1.com');
        $child2 = new T_Url_Leaf('child2','test','c2.com');

        $parent->addChild($child1);
        $child1->addChild($child2);

        ob_start();  // execute visitor
        $parent->accept($this->visitor);
        $xhtml = ob_get_contents();
        ob_end_clean();

        $expected = EOL.        // build expected output
                   '<a class="parent" href="test://p.com">parent</a>'.EOL.
                   '<ul>'.EOL.
                   $this->indent.'<li class="parent"><a class="parent" href="test://c1.com">child1</a>'.EOL.
                   $this->indent.'<ul>'.EOL.
                   str_repeat($this->indent,2).'<li><a href="test://c2.com">child2</a></li>'.EOL.
                   $this->indent.'</ul>'.EOL.
                   $this->indent.'</li>'.EOL.
                   '</ul>';

        $this->assertSame($xhtml,$expected);
    }

}
