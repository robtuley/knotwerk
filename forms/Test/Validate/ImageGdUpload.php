<?php
/**
 * Unit test cases for the T_Validate_ImageGdUpload class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ImageGdUpload unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ImageGdUpload extends T_Test_Filter_SkeletonHarness
{

    /**
     * Gets an image file.
     *
     * @param string $name
     * @param string $ext
     * @return T_File_Path
     */
    protected function getImageFile($name,$ext)
    {
        return new T_File_Path(T_ROOT_DIR.'forms/Test/_sample/',$name,$ext);
    }

    function testLoadsValidJpgFile()
    {
        $filter = new T_Validate_ImageGdUpload();
        $img = $filter->transform($this->getImageFile('valid','jpg'));
        $this->assertTrue($img instanceof T_Image_Gd);
        $this->assertSame(T_Mime::JPEG,$img->getMime()->getType());
    }

    function testFilterNormalisesTheImageUploadExtension()
    {
        $filter = new T_Validate_ImageGdUpload();
        $img = $filter->transform($this->getImageFile('valid','JpEg'));
        $this->assertTrue($img instanceof T_Image_Gd);
        $this->assertSame(T_Mime::JPEG,$img->getMime()->getType());
        $this->assertSame('jpg',$img->getMime()->getExt());
    }

    function testLoadsValidGifFile()
    {
        $filter = new T_Validate_ImageGdUpload();
        $img = $filter->transform($this->getImageFile('valid','gif'));
        $this->assertTrue($img instanceof T_Image_Gd);
        $this->assertSame(T_Mime::GIF,$img->getMime()->getType());
    }

    function testLoadsValidPngFile()
    {
        $filter = new T_Validate_ImageGdUpload();
        $img = $filter->transform($this->getImageFile('valid','png'));
        $this->assertTrue($img instanceof T_Image_Gd);
        $this->assertSame(T_Mime::PNG,$img->getMime()->getType());
    }

    function testFilterFailsIfFileDoesNotExist()
    {
        $filter = new T_Validate_ImageGdUpload();
        try {
            $filter->transform($this->getImageFile('doesnotexist','jpg'));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFilterFailsIfIsNotAImageExtension()
    {
        $filter = new T_Validate_ImageGdUpload();
        try {
            $filter->transform($this->getImageFile('notanimage','txt'));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFilterFailsIfIsTextFileMaskedAsJpeg()
    {
        $filter = new T_Validate_ImageGdUpload();
        try {
            $filter->transform($this->getImageFile('textfileasimage','jpg'));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFilterFailsIfIsJpgFileMaskedAsGifFile()
    {
        $filter = new T_Validate_ImageGdUpload();
        try {
            $filter->transform($this->getImageFile('jpgasgif','jpg'));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFilterFailsIfIsJpgFileIsMeddledWith()
    {
        $filter = new T_Validate_ImageGdUpload();
        try {
            $filter->transform($this->getImageFile('meddled','jpg'));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

}