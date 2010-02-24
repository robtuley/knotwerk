<?php
/**
 * Unit test cases for the T_Text_Header class.
 *
 * @package wikiTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Text_Header unit test cases.
 *
 * @package wikiTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Text_Header extends T_Unit_Case
{

    /**
     * Get header object to test.
     *
     * @param string $content
     * @return T_Text_Plain
     */
    function getElement($level,$content)
    {
        return new T_Text_Header($level,$content);
    }

    function testLevelCanBeSetInConstructor()
    {
        $this->assertSame(1,$this->getElement(1,'content')->getLevel());
        $this->assertSame(6,$this->getElement(6,'content')->getLevel());
    }

    function testConstructorFailsIfLevelIsLessThanOne()
    {
        try {
            $this->getElement(0,'content');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testConstructorFailsIfLevelMoreThanSix()
    {
        try {
            $this->getElement(7,'content');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testContentSetInConstructor()
    {
        $this->assertSame('content',$this->getElement(1,'content')->getContent());
    }

    function testEqualsSignUsedAsADelimiterInTitles()
    {
        $this->assertContains('== content ==',$this->getElement(1,'content')->__toString());
        $this->assertContains('======= content =======',$this->getElement(6,'content')->__toString());
    }

    function testContentIsTrimmedBeforeOutput()
    {
        $this->assertContains('== content ==',$this->getElement(1,'      content      ')->__toString());
    }

}