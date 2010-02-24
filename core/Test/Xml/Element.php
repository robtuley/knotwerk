<?php
/**
 * Unit test cases for the T_Xml_Element class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Xml_Element unit test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Xml_Element extends T_Unit_Case
{

    /**
     * Test child component of same class.
     */
    function testChildElementOfSameClass()
    {
        $xml = new T_Xml_Element('<root><child>data</child></root>');
        $child = $xml->child;
        $this->assertTrue($child instanceof T_Xml_Element);
    }

}