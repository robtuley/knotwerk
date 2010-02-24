<?php
/**
 * Unit test cases for the T_Image_GdFile class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Image_GdFile test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Image_GdFile extends T_Unit_Case
{

    /**
     * Get image directory.
     *
     * @return string
     */
    protected function getImageDir()
    {
        return T_ROOT_DIR.'core/Test/_sample/';
    }

    function testConstructorFailsWithFileNotExists()
    {
        try {
            $im = new T_Image_GdFile(new T_File_Path(T_CACHE_DIR,'image','png'));
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testConstructorFailsWithNonImageMime()
    {
        try {
            $im = new T_Image_GdFile(new T_File_Path(T_CACHE_DIR,'file','css'));
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testClassLoadsJpg()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','jpg'));
        $this->assertSame(400,$im->getWidth());
        $this->assertSame(300,$im->getHeight());
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testClassLoadsJpeg()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','jpeg'));
        $this->assertSame(400,$im->getWidth());
        $this->assertSame(300,$im->getHeight());
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testClassLoadsPng()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','png'));
        $this->assertSame(400,$im->getWidth());
        $this->assertSame(300,$im->getHeight());
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testClassLoadsGif()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','gif'));
        $this->assertSame(400,$im->getWidth());
        $this->assertSame(300,$im->getHeight());
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testInvalidJpgFailure()
    {
        try {
            $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'invalid','jpg'));
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testInvalidGifFailure()
    {
        try {
            $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'invalid','gif'));
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testInvalidPngFailure()
    {
        try {
            $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'invalid','png'));
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testConvertJpgToGif()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','jpg'));
        $im->convertTo(T_Mime::GIF);
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testConvertJpgToPng()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','jpg'));
        $im->convertTo(T_Mime::PNG);
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testConvertGifToJpg()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','gif'));
        $im->convertTo(T_Mime::JPEG);
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testConvertGifToPng()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','gif'));
        $im->convertTo(T_Mime::PNG);
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testConvertPngToGif()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','png'));
        $im->convertTo(T_Mime::GIF);
        $this->assertTrue(strlen($im->__toString())>0);
    }

    function testConvertPngToJpg()
    {
        $im = new T_Image_GdFile(new T_File_Path($this->getImageDir(),'w-400-h-300','png'));
        $im->convertTo(T_Mime::JPEG);
        $this->assertTrue(strlen($im->__toString())>0);
    }

}
