<?php
/**
 * Unit test cases for the T_Text_Paragraph class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_Paragraph unit test cases.
 *
 * @package wikiTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Text_Paragraph extends T_Test_Text_ElementWithContentHarness
{

    /**
     * Get formatted text object to test.
     *
     * @param string $content
     * @return T_Text_Plain
     */
    function getElement($content=null)
    {
        return new T_Text_Paragraph($content);
    }

    function testContentCanBeRetrievedByStringMagicMethod()
    {
        $this->assertSame('content'.EOL.EOL,$this->getElement('content')->__toString());
    }

    function testContentIsTrimmedBeforeOutput()
    {
        $this->assertSame('content'.EOL.EOL,$this->getElement('  content  ')->__toString());
    }

    function testChildContentIncludedInToStringMagicOutput()
    {
        $parent = $this->getElement('parent');
        $child = $this->getElement('child');
        $parent->addChild($child);
        $this->assertSame('parentchild'.EOL.EOL,$parent->__toString());
    }

}