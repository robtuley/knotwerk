<?php
class T_Test_Prerequisite extends T_Unit_Case
{

    function testPhpVersion()
    {
        $this->assertTrue(version_compare('5.1.3',PHP_VERSION,'<=') == 1);
    }

    function testMcryptAvailable()
    {
        $this->assertTrue(extension_loaded('mcrypt'));
    }

    function testMbstringAvailable()
    {
        $this->assertTrue(extension_loaded('mbstring'));
    }

    function testGdImageLibraryAvailable()
    {
        $this->assertTrue(extension_loaded('gd'));
    }

    function testXmlSupportIsAvailable()
    {
        $this->assertTrue(class_exists('DOMDocument'),'PHP build with no libxml2 support');
    }

}
