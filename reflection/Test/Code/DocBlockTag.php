<?php
/**
 * Unit test cases for T_Code_DocBlockTag class.
 *
 * @package reflectionTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Code_DocBlock unit test cases.
 *
 * @package reflectionTests
 */
class T_Test_Code_DocBlockTag extends T_Unit_Case
{

    function testNameAndDescAreSetInConstructor()
    {
        $tag = new T_Code_DocBlockTag('author','Rob');
        $this->assertSame($tag->getName(),'author');
        $this->assertSame($tag->getDesc(),'Rob');
    }

    function testNameAndDescCanBeFilteredOnRetrieval()
    {
        $tag = new T_Code_DocBlockTag('author','Rob');
        $f = new T_Test_Filter_Suffix('Test');
        $this->assertSame($tag->getName($f),$f->transform('author'));
        $this->assertSame($tag->getDesc($f),$f->transform('Rob'));
    }

    function testNameCanBeCaseInsensitiveQueried()
    {
        $tag = new T_Code_DocBlockTag('author','Rob');
        $this->assertTrue($tag->is('author'));
        $this->assertTrue($tag->is('AuThoR'));
        $this->assertFalse($tag->is('param'));
    }

}
