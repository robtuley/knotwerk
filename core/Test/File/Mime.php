<?php
/**
 * Unit test cases for the T_File_Mime class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_File_Mime test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_File_Mime extends T_Unit_Case
{

    function testUnknownExtSupportedAsBinaryStream()
    {
        $this->assertExtMapsToType('notanext',T_Mime::BINARY);
    }

    function testgetExtCanApplyFilterToReturnValue()
    {
        $mime = new T_File_Mime('pdf');
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($f->transform('pdf'),$mime->getExt($f));
    }

    function testExtensionCaseIsMaintained()
    {
        $mime = new T_File_Mime('PdF');
        $this->assertSame(T_Mime::PDF,$mime->getType());
        $this->assertSame('PdF',$mime->getExt());
    }

    function testInitialisedExtensionIsMaintainedUntilSetType()
    {
        $mime = new T_File_Mime('JPeg');
        $this->assertSame('JPeg',$mime->getExt());
        $mime->setType(T_Mime::JPEG);
        $this->assertSame('jpg',$mime->getExt());
    }

    /**
     * Asserts that an extension maps to a type.
     *
     * @param string $ext  file extension
     * @param int $type integer type
     * @param string $string  MIME string
     */
    protected function assertExtMapsToType($ext,$type)
    {
        $mime = new T_File_Mime($ext);
        $this->assertSame($type,$mime->getType());
        $this->assertSame($ext,$mime->getExt());  /* same as ini ext */
    }

    /**
     * Asserts that a type results a certain MIME string.
     *
     * @param int $type  MIME type
     * @param string $mime_str  MIME string
     */
    protected function assertTypeMimeString($type,$mime_str)
    {
        $mime = new T_File_Mime('jpg');
        $mime->setType($type);
        $this->assertSame($mime->__toString(),$mime_str);
    }

    /**
     * Asserts that a type maps to a particular preferred extension.
     *
     * @param int $type  MIME type
     * @param string $ext  extension
     */
    protected function assertTypePreferredExt($type,$ext)
    {
        $mime = new T_File_Mime('jpg');
        $mime->setType($type);
        $this->assertSame($mime->getExt(),$ext);
    }

    function testBinaryMimeType()
    {
        $this->assertExtMapsToType(null,T_Mime::BINARY);
        $this->assertExtMapsToType('',T_Mime::BINARY);
        $this->assertExtMapsToType('db',T_Mime::BINARY);
        $this->assertTypePreferredExt(T_Mime::BINARY,'');
        $this->assertTypeMimeString(T_Mime::BINARY,'application/octet-stream');
    }

    function testPlainTextMimeType()
    {
        $this->assertExtMapsToType('txt',T_Mime::TEXT);
        $this->assertExtMapsToType('tpl',T_Mime::TEXT);
        $this->assertTypeMimeString(T_Mime::TEXT,'text/plain');
        $this->assertTypePreferredExt(T_Mime::TEXT,'txt');
    }

    function testXhtmlMimeType()
    {
        $this->assertExtMapsToType('htm',T_Mime::XHTML);
        $this->assertExtMapsToType('html',T_Mime::XHTML);
        $this->assertTypeMimeString(T_Mime::XHTML,'text/html');
        $this->assertTypePreferredExt(T_Mime::XHTML,'htm');
    }

    function testCssMimeType()
    {
        $this->assertExtMapsToType('css',T_Mime::CSS);
        $this->assertTypeMimeString(T_Mime::CSS,'text/css');
        $this->assertTypePreferredExt(T_Mime::CSS,'css');
    }

    function testXmlMimeType()
    {
        $this->assertExtMapsToType('xml',T_Mime::XML);
        $this->assertTypeMimeString(T_Mime::XML,'application/xml');
        $this->assertTypePreferredExt(T_Mime::XML,'xml');
    }

    function testWordMimeType()
    {
        $this->assertExtMapsToType('docx',T_Mime::WORD);
        $this->assertExtMapsToType('doc',T_Mime::WORD);
        $this->assertTypeMimeString(T_Mime::WORD,'application/msword');
        $this->assertTypePreferredExt(T_Mime::WORD,'doc');
    }

    function testExcelMimeType()
    {
        $this->assertExtMapsToType('xlsx',T_Mime::EXCEL);
        $this->assertExtMapsToType('xls',T_Mime::EXCEL);
        $this->assertTypeMimeString(T_Mime::EXCEL,'application/excel');
        $this->assertTypePreferredExt(T_Mime::EXCEL,'xls');
    }

    function testPdfMimeType()
    {
        $this->assertExtMapsToType('pdf',T_Mime::PDF);
        $this->assertTypeMimeString(T_Mime::PDF,'application/pdf');
        $this->assertTypePreferredExt(T_Mime::PDF,'pdf');
    }

    function testJpegMimeType()
    {
        $this->assertExtMapsToType('jpeg',T_Mime::JPEG);
        $this->assertExtMapsToType('jpg',T_Mime::JPEG);
        $this->assertTypeMimeString(T_Mime::JPEG,'image/jpeg');
        $this->assertTypePreferredExt(T_Mime::JPEG,'jpg');
    }

    function testPngMimeType()
    {
        $this->assertExtMapsToType('png',T_Mime::PNG);
        $this->assertTypeMimeString(T_Mime::PNG,'image/png');
        $this->assertTypePreferredExt(T_Mime::PNG,'png');
    }

    function testGifMimeType()
    {
        $this->assertExtMapsToType('gif',T_Mime::GIF);
        $this->assertTypeMimeString(T_Mime::GIF,'image/gif');
        $this->assertTypePreferredExt(T_Mime::GIF,'gif');
    }

    function testPhpMimeType()
    {
        $this->assertExtMapsToType('php',T_Mime::PHP);
        $this->assertExtMapsToType('php5',T_Mime::PHP);
        $this->assertExtMapsToType('phps',T_Mime::PHP);
        $this->assertTypeMimeString(T_Mime::PHP,'application/x-httpd-php');
        $this->assertTypePreferredExt(T_Mime::PHP,'php');
    }

    function testZipMimeType()
    {
        $this->assertExtMapsToType('zip',T_Mime::ZIP);
        $this->assertTypeMimeString(T_Mime::ZIP,'application/zip');
        $this->assertTypePreferredExt(T_Mime::ZIP,'zip');
    }

    function testJavascriptMimeType()
    {
        $this->assertExtMapsToType('js',T_Mime::JS);
        $this->assertTypeMimeString(T_Mime::JS,'text/javascript');
        $this->assertTypePreferredExt(T_Mime::JS,'js');
    }

    function testExtensionComparisonIsCaseInsensitive()
    {
        $mime = new T_File_Mime('cSS');
        $this->assertSame($mime->getType(),T_Mime::CSS);
    }

    function testCanModifyMimeType()
    {
        $mime = new T_File_Mime('jpg');
        $mime->setType(T_Mime::CSS);
        $this->assertSame($mime->getType(),T_Mime::CSS);
        $this->assertSame($mime->getExt(),'css');
        $this->assertSame($mime->__toString(),'text/css');
    }

    function testSetTypeHasAFluentInterface()
    {
        $mime = new T_File_Mime('jpg');
        $test = $mime->setType(T_Mime::CSS);
        $this->assertSame($mime,$test);
    }

    function testSetTypeFailsWithIllegalType()
    {
        $mime = new T_File_Mime('jpg');
        try {
            $mime->setType(-1);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testSetTypeFailsWithValidMimeTypeNotValidAsFile()
    {
        $mime = new T_File_Mime('jpg');
        try {
            $mime->setType(T_Mime::FORM_URL_ENCODED);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

}