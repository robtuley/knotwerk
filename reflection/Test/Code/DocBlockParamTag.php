<?php
/**
 * Unit test cases for T_Code_DocBlockParamTag class.
 *
 * @package reflectionTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Code_DocBlockParamTag unit test cases.
 *
 * @package reflectionTests
 */
class T_Test_Code_DocBlockParamTag extends T_Unit_Case
{

    function testNameDescAndTypeSetInContructor()
    {
        $tag = new T_Code_DocBlockParamTag('var','string[]','$arg','desc');
        $this->assertSame($tag->getName(),'var');
        $this->assertSame($tag->getDesc(),'desc');
        $this->assertSame($tag->getType(),'string');
        $this->assertTrue($tag->isArray());
        $this->assertSame($tag->getCombinedType(),'string[]');
    }

    function testVarSetInContructor()
    {
        $tag = new T_Code_DocBlockParamTag('var','string[]','$arg','desc');
        $this->assertSame($tag->getVar(),'$arg');
    }

    function testTypeCanBeFiltered()
    {
        $tag = new T_Code_DocBlockParamTag('var','string[]','$arg','desc');
        $f = new T_Test_Filter_Suffix('Test');
        $this->assertSame($tag->getVar($f),$f->transform('$arg'));
    }

}
