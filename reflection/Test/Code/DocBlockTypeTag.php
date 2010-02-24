<?php
/**
 * Unit test cases for T_Code_DocBlockTypeTag class.
 *
 * @package reflectionTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Code_DocBlockTypeTag unit test cases.
 *
 * @package reflectionTests
 */
class T_Test_Code_DocBlockTypeTag extends T_Unit_Case
{

    function testNameAsDescSetInContructor()
    {
        $tag = new T_Code_DocBlockTypeTag('var','string','desc');
        $this->assertSame($tag->getName(),'var');
        $this->assertSame($tag->getDesc(),'desc');
    }

    function testNormalTypeSetAsNormal()
    {
        $tag = new T_Code_DocBlockTypeTag('var','string','desc');
        $this->assertSame($tag->getType(),'string');
        $this->assertFalse($tag->isArray());
        $this->assertSame($tag->getCombinedType(),'string');
    }

    function testUnknownTypeSetAsNormal()
    {
        $tag = new T_Code_DocBlockTypeTag('var',null,'desc');
        $this->assertSame($tag->getType(),null);
        $this->assertFalse($tag->isArray());
        $this->assertSame($tag->getCombinedType(),'unknown');
    }

    function testArraySetAsUnknownArray()
    {
        $tag = new T_Code_DocBlockTypeTag('var','array','desc');
        $this->assertSame($tag->getType(),null);
        $this->assertTrue($tag->isArray());
        $this->assertSame($tag->getCombinedType(),'unknown[]');
    }

    function testArrayAndTypeInfoIsParsedCorrectly()
    {
        $tag = new T_Code_DocBlockTypeTag('var','T_Url[]','desc');
        $this->assertSame($tag->getType(),'T_Url');
        $this->assertTrue($tag->isArray());
        $this->assertSame($tag->getCombinedType(),'T_Url[]');
    }

    function testTypeCanBeFiltered()
    {
        $tag = new T_Code_DocBlockTypeTag('var','string[]','desc');
        $f = new T_Test_Filter_Suffix('Test');
        $this->assertSame($tag->getType($f),$f->transform('string'));
        $this->assertSame($tag->getCombinedType($f),$f->transform('string[]'));
    }

}
