<?php
/**
 * Unit test cases for the T_Mime class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Mime unit test cases.
 *
 * @package coreTests
 */
class T_Test_Mime extends T_Unit_Case
{

    function testMimeReturnsTypeFromConstructor()
    {
        $mime = new T_Mime(T_Mime::GIF);
        $this->assertSame($mime->getType(),T_Mime::GIF);
    }

    function testFailureIfCreatedWithInvalidType()
    {
        try {
            $mime = new T_Mime('not_a_mime');
            $this->fail();
        } catch (InvalidArgumentException $e) {}
    }

    function testEachMimeConstantIsUnique()
    {
        $reflect = new ReflectionClass('T_Mime');
        $constants = $reflect->getConstants();
        $values = array();
        foreach ($constants as $key => $int) {
            $this->assertTrue(is_integer($int),"$key is int");
            $this->assertFalse(isset($values[$int]),"$key is repeated");
            $values[$int] = true;
        }
    }

    function testConstantsAreAllInUppercase()
    {
        $reflect = new ReflectionClass('T_Mime');
        $constants = $reflect->getConstants();
        foreach ($constants as $name => $int) {
            $this->assertSame(strcmp($name,strtoupper($name)),0,"$name is uppercase");
        }
    }

    function testEachMimeTypeRelatesToAString()
    {
        $reflect = new ReflectionClass('T_Mime');
        $constants = $reflect->getConstants();
        foreach ($constants as $key => $type) {
            $mime = new T_Mime($type);
            $str = $mime->__toString();
            $this->assertTrue(is_string($str),"$key is string mime");
            $this->assertTrue(strlen($str)>0,"$key mime str has length");
        }
    }

    function testCanCreateMimeFromString()
    {
        $this->assertEquals(new T_Mime(T_Mime::JPEG),T_Mime::getByString('image/jpeg'));
        $this->assertEquals(new T_Mime(T_Mime::XHTML),T_Mime::getByString('text/html'));
    }

    function testCanCreateSameMimeFromMoreThanOneDiffMimeString()
    {
        $this->assertEquals(new T_Mime(T_Mime::XML),T_Mime::getByString('text/xml'));
        $this->assertEquals(new T_Mime(T_Mime::XML),T_Mime::getByString('application/xml'));
    }

    function testBinaryIsDefaultIfCannotMatchString()
    {
        $this->assertEquals(new T_Mime(T_Mime::BINARY),T_Mime::getByString('not_a_mime'));
    }

}