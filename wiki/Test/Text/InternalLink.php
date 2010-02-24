<?php
/**
 * Unit test cases for the T_Text_InternalLink class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_InternalLink unit test cases.
 *
 * @package wikiTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Text_InternalLink extends T_Test_Text_ElementWithContentHarness
{

    /**
     * Get external link object object to test.
     *
     * @param string $content
     * @return T_Text_ExternalLink
     */
    function getElement($content=null,$url=null)
    {
        return new T_Text_InternalLink($content,$url);
    }

    function testContentCanBeRetrievedByStringMagicMethod()
    {
        $this->assertSame('[url content]',$this->getElement('content','url')->__toString());
    }

    function testChildContentIncludedInToStringMagicOutput()
    {
        $parent = $this->getElement('parent','url');
        $child = new T_Text_Plain('child');
        $parent->addChild($child);
        $this->assertSame('[url parentchild]',$parent->__toString());
    }

    function testContentIsNotTrimmed()
    {
        $element = $this->getElement('  content  ','url');
        $this->assertSame('  content  ',$element->getContent());
        $this->assertSame('[url   content  ]',$element->__toString());
    }

    function testUrlIsSetInConstructor()
    {
        $element = $this->getElement('content','url');
        $this->assertSame('url',$element->getUrl());
    }

    function testUrlCanBeFiltered()
    {
        $element = $this->getElement('content','url');
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($f->transform('url'),$element->getUrl($f));
    }

}