<?php
/**
 * Unit test cases for the T_Text_ListLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_ListLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_ListLexer extends T_Unit_Case
{

    function testLeavesNoListContentAlone()
    {
        $text = new T_Text_Plain("Some\n\ncontent that will be\n\nleft **alone** and -not \n\n changed");
        $expect = clone($text);
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testAvoidsClashWithEmphTextAtStartOfLine()
    {
        $text = new T_Text_Plain("** Bold Content ** at the start of the line");
        $expect = clone($text);
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testPutsASingleOrderedListItemInAListContainer()
    {
        $text = new T_Text_Plain(T_Text_List::ORDERED." Item #1");
        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List(T_Text_List::ORDERED));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testPutsASingleUnorderedListItemInAListContainer()
    {
        $text = new T_Text_Plain(T_Text_List::UNORDERED." Item #1");
        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List(T_Text_List::UNORDERED));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesAListWithMultipleItems()
    {
        $type = T_Text_List::ORDERED;
        $text = new T_Text_Plain("$type Item #1\n$type Item #2\n$type Item #3");
        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List($type));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $list->addChild(new T_Text_ListItem('Item #2'));
        $list->addChild(new T_Text_ListItem('Item #3'));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesAListWithPriorContent()
    {
        $type = T_Text_List::ORDERED;
        $text = new T_Text_Plain("Prior Iñtërnâtiônàlizætiøn content\n$type Item #1\n$type Item #2\n$type Item #3");
        $expect = new T_Text_Plain(null);
        $expect->addChild(new T_Text_Plain("Prior Iñtërnâtiônàlizætiøn content"));
        $expect->addChild($list=new T_Text_List($type));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $list->addChild(new T_Text_ListItem('Item #2'));
        $list->addChild(new T_Text_ListItem('Item #3'));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesAListWithPostContent()
    {
        $type = T_Text_List::ORDERED;
        $text = new T_Text_Plain("\n$type Item #1\n$type Item #2\n$type Item #3\n\nPost Iñtërnâtiônàlizætiøn content");
        $expect = new T_Text_Plain(null);
        $expect->addChild($list=new T_Text_List($type));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $list->addChild(new T_Text_ListItem('Item #2'));
        $list->addChild(new T_Text_ListItem('Item #3'));
        $expect->addChild(new T_Text_Plain("Post Iñtërnâtiônàlizætiøn content"));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesAListWithPriorAndPostContent()
    {
        $type = T_Text_List::ORDERED;
        $text = new T_Text_Plain("Prior Iñtërnâtiônàlizætiøn content\n$type Item #1\n$type Item #2\n$type Item #3\n\nPost Iñtërnâtiônàlizætiøn content");
        $expect = new T_Text_Plain(null);
        $expect->addChild(new T_Text_Plain("Prior Iñtërnâtiônàlizætiøn content"));
        $expect->addChild($list=new T_Text_List($type));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $list->addChild(new T_Text_ListItem('Item #2'));
        $list->addChild(new T_Text_ListItem('Item #3'));
        $expect->addChild(new T_Text_Plain("Post Iñtërnâtiônàlizætiøn content"));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesMultipleListsInOneBlock()
    {
        $type1 = T_Text_List::ORDERED;
        $type2 = T_Text_List::UNORDERED;
        $text = "Prior Iñtërnâtiônàlizætiøn content\r\n".
                "$type1 List1 #1\n$type1 List1 #2\n$type1 List1 #3\n".
                "Middle Iñtërnâtiônàlizætiøn content\n".
                "$type2 List2 #1\n$type2 List2 #2\n$type2 List2 #3\n\n".
                "Post Iñtërnâtiônàlizætiøn content";
        $text = new T_Text_Plain($text);

        $expect = new T_Text_Plain(null);
        $expect->addChild(new T_Text_Plain("Prior Iñtërnâtiônàlizætiøn content"));
        $expect->addChild($list=new T_Text_List($type1));
        $list->addChild(new T_Text_ListItem('List1 #1'));
        $list->addChild(new T_Text_ListItem('List1 #2'));
        $list->addChild(new T_Text_ListItem('List1 #3'));
        $expect->addChild(new T_Text_Plain("Middle Iñtërnâtiônàlizætiøn content"));
        $expect->addChild($list=new T_Text_List($type2));
        $list->addChild(new T_Text_ListItem('List2 #1'));
        $list->addChild(new T_Text_ListItem('List2 #2'));
        $list->addChild(new T_Text_ListItem('List2 #3'));
        $expect->addChild(new T_Text_Plain("Post Iñtërnâtiônàlizætiøn content"));

        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesASingleNestedList()
    {
        $type = T_Text_List::ORDERED;
        $text = new T_Text_Plain("$type Item #1\n  $type Item #2");

        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List($type));
        $list->addChild($item=new T_Text_ListItem('Item #1'));
        $item->addChild($nest=new T_Text_List($type));
        $nest->addChild(new T_Text_ListItem('Item #2'));

        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesANestedListWithMultipleItemsAndReturnToMainList()
    {
        $type1 = T_Text_List::ORDERED;
        $type2 = T_Text_List::UNORDERED;
        $text = new T_Text_Plain("$type1 Item #1\n  $type2 Item #2\n  $type2 Item #3\n  $type2 Item #4\n$type1 Item #5");

        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List($type1));
        $list->addChild($item=new T_Text_ListItem('Item #1'));
        $item->addChild($nest=new T_Text_List($type2));
        $nest->addChild(new T_Text_ListItem('Item #2'));
        $nest->addChild(new T_Text_ListItem('Item #3'));
        $nest->addChild(new T_Text_ListItem('Item #4'));
        $list->addChild(new T_Text_ListItem('Item #5'));

        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesANestedListWithScrewedUpIndent()
    {
        $type1 = T_Text_List::ORDERED;
        $type2 = T_Text_List::UNORDERED;
        $text = new T_Text_Plain("$type1 Item #1\n    $type2 Item #2\n  $type2 Item #3\n   $type2 Item #4\n$type1 Item #5");

        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List($type1));
        $list->addChild($item=new T_Text_ListItem('Item #1'));
        $item->addChild($nest=new T_Text_List($type2));
        $nest->addChild(new T_Text_ListItem('Item #2'));
        $nest->addChild(new T_Text_ListItem('Item #3'));
        $nest->addChild(new T_Text_ListItem('Item #4'));
        $list->addChild(new T_Text_ListItem('Item #5'));

        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesAMultipleNestedList()
    {
        $type1 = T_Text_List::ORDERED;
        $type2 = T_Text_List::UNORDERED;
        $text = new T_Text_Plain("$type1 Item #1\n  $type2 Item #2\n    $type2 Item #3\n  $type2 Item #4\n$type1 Item #5");

        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List($type1));
        $list->addChild($item=new T_Text_ListItem('Item #1'));
        $item->addChild($nest=new T_Text_List($type2));
        $nest->addChild($item2=new T_Text_ListItem('Item #2'));
        $item2->addChild($nest2=new T_Text_List($type2));
        $nest2->addChild(new T_Text_ListItem('Item #3'));
        $nest->addChild(new T_Text_ListItem('Item #4'));
        $list->addChild(new T_Text_ListItem('Item #5'));

        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesARepeatedlyNestedList()
    {
        $type1 = T_Text_List::ORDERED;
        $type2 = T_Text_List::UNORDERED;
        $content = <<<CONTENT
          $type1 Item #1
            $type2 Item #2
            $type2 Item #3
          $type1 Item #4
          $type1 Item #5
            $type2 Item #6
            $type2 Item #7
          $type1 Item #8
CONTENT;
        $text = new T_Text_Plain($content);

        $expect = new T_Text_Plain();
        $expect->addChild($list=new T_Text_List($type1));
        $list->addChild($item=new T_Text_ListItem('Item #1'));
        $item->addChild($nest=new T_Text_List($type2));
        $nest->addChild(new T_Text_ListItem('Item #2'));
        $nest->addChild(new T_Text_ListItem('Item #3'));
        $list->addChild(new T_Text_ListItem('Item #4'));
        $list->addChild($item2=new T_Text_ListItem('Item #5'));
        $item2->addChild($nest2=new T_Text_List($type2));
        $nest2->addChild(new T_Text_ListItem('Item #6'));
        $nest2->addChild(new T_Text_ListItem('Item #7'));
        $list->addChild(new T_Text_ListItem('Item #8'));

        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

    function testCannotCreateLexerWithNoTypes()
    {
        try {
            $lexer = new T_Text_ListLexer(0);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testLeavesHeaderContentAlone()
    {
        $text = T_Text_List::UNORDERED.' Item #1';
        $header = new T_Text_Header(3,$text);
        $expect = clone($header);
        $header->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$header);
    }

    function testParsesInsideAQuoteBlock()
    {
        $text = new T_Text_Quote('some citation',T_Text_List::ORDERED." Item #1");
        $expect = new T_Text_Quote('some citation',null);
        $expect->addChild($list=new T_Text_List(T_Text_List::ORDERED));
        $list->addChild(new T_Text_ListItem('Item #1'));
        $text->accept(new T_Text_ListLexer());
        $this->assertEquals($expect,$text);
    }

}
