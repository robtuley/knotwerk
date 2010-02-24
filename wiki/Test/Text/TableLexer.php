<?php
/**
 * Unit test cases for the T_Text_TableLexer class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_TableLexer unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_TableLexer extends T_Unit_Case
{

    function testLeavesNoTableContentAlone()
    {
        $text = new T_Text_Plain("Some\n\ncontent that will be\n------\nleft **alone** and -not \n\n changed");
        $expect = clone($text);
        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesSingleMinimalNoHeaderTableIntoSingleTableContainer()
    {
        $text = new T_Text_Plain("| 1 | 2 |\n| 3 | 4 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))->addChild(new T_Text_TableCell('2'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3'))->addChild(new T_Text_TableCell('4'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testStartAndEndDelimitersWithPlusesOrJustDashesAreIgnored()
    {
        $template = "| 1 | 2 |\n| 3 | 4 |";

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))->addChild(new T_Text_TableCell('2'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3'))->addChild(new T_Text_TableCell('4'));

        $text = new T_Text_Plain("+---+---+\n$template\n+---+---+");
        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);

        $text = new T_Text_Plain("---------\n$template\n---------");
        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testDelimiterLineUnderFirstRowIndicatesItShouldBeAHeader()
    {
        $text = new T_Text_Plain("+---+---+\n| 1 | 2 |\n+---+---+\n| 3 | 4 |\n+---+---+");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1',T_Text_TableCell::HEADER))
            ->addChild(new T_Text_TableCell('2',T_Text_TableCell::HEADER));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3'))->addChild(new T_Text_TableCell('4'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testCanExplicitallySetHeaderCellsUsingTheUpArrow()
    {
        $text = new T_Text_Plain("|^ 1 | 2 |\n|^ 3 | 4 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1',T_Text_TableCell::HEADER))
            ->addChild(new T_Text_TableCell('2'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3',T_Text_TableCell::HEADER))
            ->addChild(new T_Text_TableCell('4'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testSpacePaddingAtLineFrontEndAndInCellsIsRemoved()
    {
        $text = new T_Text_Plain("  +---+---+\n    |     1 | 2     |  \n   +---+---+\n   |    3 | 4    |   \n    +---+---+    ");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1',T_Text_TableCell::HEADER))
            ->addChild(new T_Text_TableCell('2',T_Text_TableCell::HEADER));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3'))->addChild(new T_Text_TableCell('4'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testMinimalTableWithInternationalCharacters()
    {
        $text = new T_Text_Plain("| Iñtërnât | iônàlizætiøn |\n| Iñtër | lizætiøn |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('Iñtërnât'))->addChild(new T_Text_TableCell('iônàlizætiøn'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('Iñtër'))->addChild(new T_Text_TableCell('lizætiøn'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesSingleLineTable()
    {
        $text = new T_Text_Plain("| 1 | 2 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))->addChild(new T_Text_TableCell('2'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testSpansLastCellInRowsWithWrongCount()
    {
        $text = new T_Text_Plain("| 1 | 2 |\n| 3 | 4 | 5 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))
            ->addChild(new T_Text_TableCell('2',T_Text_TableCell::PLAIN,2));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3'))
            ->addChild(new T_Text_TableCell('4'))
            ->addChild(new T_Text_TableCell('5'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testSingleCellSpanInCentre()
    {
        $text = new T_Text_Plain("| 1 | 2 || 3 |\n| 4 | 5 | 6 | 7 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))
            ->addChild(new T_Text_TableCell('2',T_Text_TableCell::PLAIN,2))
            ->addChild(new T_Text_TableCell('3'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('4'))
            ->addChild(new T_Text_TableCell('5'))
            ->addChild(new T_Text_TableCell('6'))
            ->addChild(new T_Text_TableCell('7'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testNoSpanWhenWhitespacePresentInCell()
    {
        $text = new T_Text_Plain("| 1 | 2 | | 3 |\n| 4 | 5 | 6 | 7 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))
            ->addChild(new T_Text_TableCell('2'))
            ->addChild(new T_Text_TableCell(''))
            ->addChild(new T_Text_TableCell('3'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('4'))
            ->addChild(new T_Text_TableCell('5'))
            ->addChild(new T_Text_TableCell('6'))
            ->addChild(new T_Text_TableCell('7'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testDoubleCellSpanInCentre()
    {
        $text = new T_Text_Plain("| 1 ||| 3 |\n| 4 | 5 | 6 | 7 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1',T_Text_TableCell::PLAIN,3))
            ->addChild(new T_Text_TableCell('3'));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('4'))
            ->addChild(new T_Text_TableCell('5'))
            ->addChild(new T_Text_TableCell('6'))
            ->addChild(new T_Text_TableCell('7'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testSingleCellSpanAtEnd()
    {
        $text = new T_Text_Plain("| 1 | 2 |3 ||\n| 4 | 5 | 6 | 7 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))
            ->addChild(new T_Text_TableCell('2'))
            ->addChild(new T_Text_TableCell('3',T_Text_TableCell::PLAIN,2));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('4'))
            ->addChild(new T_Text_TableCell('5'))
            ->addChild(new T_Text_TableCell('6'))
            ->addChild(new T_Text_TableCell('7'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testDoubleCellSpanAtEnd()
    {
        $text = new T_Text_Plain("| 1 | 3 |||\n| 4 | 5 | 6 | 7 |");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))
            ->addChild(new T_Text_TableCell('3',T_Text_TableCell::PLAIN,3));
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('4'))
            ->addChild(new T_Text_TableCell('5'))
            ->addChild(new T_Text_TableCell('6'))
            ->addChild(new T_Text_TableCell('7'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesTableWithPriorContent()
    {
        $text = new T_Text_Plain("Iñtërnâtiônàlizætiøn\n| lizætiøn | Iñtër |");

        $expect = new T_Text_Plain();
        $expect->addChild(new T_Text_Plain('Iñtërnâtiônàlizætiøn'));
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('lizætiøn'))
            ->addChild(new T_Text_TableCell('Iñtër'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesTableWithPostContent()
    {
        $text = new T_Text_Plain("| lizætiøn | Iñtër |\nIñtërnâtiônàlizætiøn");

        $expect = new T_Text_Plain();
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('lizætiøn'))
            ->addChild(new T_Text_TableCell('Iñtër'));
        $expect->addChild(new T_Text_Plain('Iñtërnâtiônàlizætiøn'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesTableWithPriorAndPostContent()
    {
        $text = new T_Text_Plain("Iñtërnât\n| lizætiøn | Iñtër |\niônàlizætiøn");

        $expect = new T_Text_Plain();
        $expect->addChild(new T_Text_Plain('Iñtërnât'));
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('lizætiøn'))
            ->addChild(new T_Text_TableCell('Iñtër'));
        $expect->addChild(new T_Text_Plain('iônàlizætiøn'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testTablesSeparatedByLineReturnsRenderedAsSeparateTables()
    {
        $text1 = new T_Text_Plain("| 1 | 2 |\n\n| 3 | 4 |");
        $text2 = new T_Text_Plain("| 1 | 2 |\n\n\n\n\n\n| 3 | 4 |");

        $expect = new T_Text_Plain();

        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))
            ->addChild(new T_Text_TableCell('2'));

        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('3'))
            ->addChild(new T_Text_TableCell('4'));

        $text1->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text1,'double line break');
        $text2->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text2,'lots of line breaks');
    }

    function testParsesMultipleTablesInOneBlock()
    {
        $text = new T_Text_Plain("| lizætiøn | Iñtër |\niônàl\n| ætiøn | Iñt |");

        $expect = new T_Text_Plain();

        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('lizætiøn'))
            ->addChild(new T_Text_TableCell('Iñtër'));

        $expect->addChild(new T_Text_Plain('iônàl'));

        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('ætiøn'))
            ->addChild(new T_Text_TableCell('Iñt'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesMultipleTablesInOneBlockWithPriorAndPostContent()
    {
        $text = new T_Text_Plain("Iñtërnât\n| lizætiøn | Iñtër |\niônàl\n| ætiøn | Iñt |\nizætiøn");

        $expect = new T_Text_Plain();
        $expect->addChild(new T_Text_Plain('Iñtërnât'));

        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('lizætiøn'))
            ->addChild(new T_Text_TableCell('Iñtër'));

        $expect->addChild(new T_Text_Plain('iônàl'));

        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('ætiøn'))
            ->addChild(new T_Text_TableCell('Iñt'));

        $expect->addChild(new T_Text_Plain('izætiøn'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

    function testParsesInsideAQuoteBlock()
    {
        $text = new T_Text_Quote('some citation','| 1 | 2 |');

        $expect = new T_Text_Quote('some citation',null);
        $expect->addChild($table=new T_Text_Table());
        $table->addChild($row=new T_Text_TableRow());
        $row->addChild(new T_Text_TableCell('1'))->addChild(new T_Text_TableCell('2'));

        $text->accept(new T_Text_TableLexer());
        $this->assertEquals($expect,$text);
    }

}