<?php
/**
 * Unit test cases for T_Code_DocBlock class.
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
class T_Test_Code_DocBlock extends T_Unit_Case
{

    protected $eol = EOL;

    protected function getBlockStart($desc)
    {
        return '/**'.$this->eol.' * '.$desc.$this->eol.' * '.$this->eol;
    }

    protected function getBlockEnd()
    {
        return ' */';
    }

    protected function getBlockLine($line)
    {
        return ' * '.$line.$this->eol;
    }

    function testMinimalDocBlock()
    {
        $block = $this->getBlockStart('Short desc.').
                 $this->getBlockEnd();
        $doc = new T_Code_DocBlock($block);
        $this->assertSame($doc->getSummary(),'Short desc.');
        $this->assertSame($doc->getDesc(),null);
        $this->assertSame($doc->getTags(),array());
    }

    function testShortAndSingleLineLongDescriptionDocBlock()
    {
        $block = $this->getBlockStart('Short desc.').
                 $this->getBlockLine('Long Desc.').
                 $this->getBlockEnd();
        $doc = new T_Code_DocBlock($block);
        $this->assertSame($doc->getSummary(),'Short desc.');
        $this->assertSame($doc->getDesc(),'Long Desc.');
        $this->assertSame($doc->getTags(),array());
    }

    function testLineBreaksArePreservedInMiddleOfLongDesc()
    {
        $block = $this->getBlockStart('Short desc.').
                 $this->getBlockLine('').
                 $this->getBlockLine('Line 1').
                 $this->getBlockLine('').
                 $this->getBlockLine('Line 3').
                 $this->getBlockLine('Line 4').
                 $this->getBlockEnd();
        $doc = new T_Code_DocBlock($block);
        $this->assertSame($doc->getSummary(),'Short desc.');
        $this->assertSame($doc->getDesc(),'Line 1'.EOL.EOL.'Line 3'.EOL.'Line 4');
        $this->assertSame($doc->getTags(),array());
    }

    function testDocBlockTagsAreParsed()
    {
        $block = $this->getBlockStart('Short desc.').
                 $this->getBlockLine('').
                 $this->getBlockLine('@var type1  var desc').
                 $this->getBlockLine('@var type2').
                 $this->getBlockLine('@var array[]').
                 $this->getBlockLine('@return type3  return desc').
                 $this->getBlockLine('@return type4').
                 $this->getBlockLine('@see http://some/url').
                 $this->getBlockLine('@param type5 $var_1   var 1 desc  ').
                 $this->getBlockLine('@param type6 $var_2').
                 $this->getBlockLine('@param type7[] $var_3').
                 $this->getBlockLine('@param not valid (skipped)').
                 $this->getBlockEnd();
        $doc = new T_Code_DocBlock($block);
        $this->assertSame($doc->getSummary(),'Short desc.');
        $this->assertSame($doc->getDesc(),null);
        $expect = array( new T_Code_DocBlockTypeTag('var','type1','var desc'),
                         new T_Code_DocBlockTypeTag('var','type2',null),
                         new T_Code_DocBlockTypeTag('var','array[]',null),
                         new T_Code_DocBlockTypeTag('return','type3','return desc'),
                         new T_Code_DocBlockTypeTag('return','type4',null),
                         new T_Code_DocBlockTag('see','http://some/url'),
                         new T_Code_DocBlockParamTag('param','type5','var_1','var 1 desc'),
                         new T_Code_DocBlockParamTag('param','type6','var_2',null),
                         new T_Code_DocBlockParamTag('param','type7[]','var_3',null),
                         );
        $this->assertEquals($doc->getTags(),$expect);
    }

}
