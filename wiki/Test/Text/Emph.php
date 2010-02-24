<?php
/**
 * Unit test cases for the T_Text_Emph class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_Emph unit test cases.
 *
 * @package wikiTests
 */
class T_Test_Text_Emph extends T_Test_Text_ElementWithContentHarness
{

    /**
     * Get text object to test.
     *
     * @param string $content
     * @return T_Text_Plain
     */
    function getElement($content=null)
    {
        return new T_Text_Emph($content);
    }

    function testContentCanBeRetrievedByStringMagicMethod()
    {
        $this->assertSame('content',$this->getElement('content')->__toString());
    }

    function testChildContentIncludedInToStringMagicOutput()
    {
        $parent = $this->getElement('parent');
        $child = $this->getElement('child');
        $parent->addChild($child);
        $this->assertSame('parentchild',$parent->__toString());
    }

}