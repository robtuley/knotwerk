<?php
/**
 * Unit test cases for the T_Filter_TextTable class.
 *
 * @package unitTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_TextTable test cases.
 *
 * @package unitTests
 */
class T_Test_Filter_TextTable extends T_Test_Filter_SkeletonHarness
{

    function testRendersAMultiRowMultiColTable()
    {
        $filter = new T_Filter_TextTable();
        $data = array( array('1st Title','2nd Title'),
                       array('data',     'more which is longer'),
                       array('data 2',   'some extra')    );
        $expected = '+-----------+----------------------+'.EOL.
                    '| 1st Title | 2nd Title            |'.EOL.
                    '+-----------+----------------------+'.EOL.
                    '| data      | more which is longer |'.EOL.
                    '| data 2    | some extra           |'.EOL.
                    '+-----------+----------------------+';
        $this->assertSame($filter->transform($data),$expected);
    }

    function testRendersASingleRowMultiColTable()
    {
        $filter = new T_Filter_TextTable();
        $data = array( array('1st Title','2nd Title') );
        $expected = '+-----------+-----------+'.EOL.
                    '| 1st Title | 2nd Title |'.EOL.
                    '+-----------+-----------+';
        $this->assertSame($filter->transform($data),$expected);
    }

    function testRendersAMultiRowSingleColTable()
    {
        $filter = new T_Filter_TextTable();
        $data = array( array('1st Title'),
                       array('some really really long data'),
                       array('data 2')   );
        $expected = '+------------------------------+'.EOL.
                    '| 1st Title                    |'.EOL.
                    '+------------------------------+'.EOL.
                    '| some really really long data |'.EOL.
                    '| data 2                       |'.EOL.
                    '+------------------------------+';
        $this->assertSame($filter->transform($data),$expected);
    }

    function testRendersASingleRowSingleColTable()
    {
        $filter = new T_Filter_TextTable();
        $data = array( array('1st Title') );
        $expected = '+-----------+'.EOL.
                    '| 1st Title |'.EOL.
                    '+-----------+';
        $this->assertSame($filter->transform($data),$expected);
    }

}