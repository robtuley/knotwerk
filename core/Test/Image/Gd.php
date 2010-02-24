<?php
class T_Test_Image_Gd extends T_Unit_Case
{

    function testWidthAndHeightSetInConstructor()
    {
        $im = new T_Image_Gd(100,200);
        $this->assertSame($im->getWidth(),100);
        $this->assertSame($im->getHeight(),200);
    }

    function testGetMimeReturnsMimeObject()
    {
        $im = New T_Image_Gd(100,200);
        $this->assertTrue($im->getMime() instanceof T_File_Mime);
    }

    function testDefaultTypeIsPng()
    {
        $im = new T_Image_Gd(100,200);
        $this->assertSame($im->getMime()->getType(),T_Mime::PNG);
    }

    function testCanSetTypeInConstructor()
    {
        $im = new T_Image_Gd(100,200,T_Mime::JPEG);
        $this->assertSame($im->getMime()->getType(),T_Mime::JPEG);
    }

    function testConstructorFailure()
    {
        try {
            $im = new T_Image_Gd(100,-10);
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testImageResourceRefCanBeAccessed()
    {
        $im = new T_Image_Gd(100,200);
        $this->assertTrue(is_resource($im->handle));
        $im->handle = false; /* should affect actual variable */
        $this->assertFalse($im->handle);
    }

    function testConvertToChangesMimeType()
    {
        $im = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::JPEG);
        $this->assertSame($im->getMime()->getType(),T_Mime::JPEG);
    }

    function testConvertToHasAFluentInterface()
    {
        $im = new T_Image_Gd(100,200);
        $test = $im->convertTo(T_Mime::JPEG);
        $this->assertSame($test,$im);
    }

    function testCanSavePngToNewFile()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::PNG)->toFile($dir->__toString(),'image');
        $this->assertEquals($path,
                            new T_File_Path($dir->__toString(),'image','png'));
        $this->assertTrue($path->exists());
    }

    function testCanSaveGifToNewFile()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::GIF)->toFile($dir->__toString(),'image');
        $this->assertEquals($path,
                            new T_File_Path($dir->__toString(),'image','gif'));
        $this->assertTrue($path->exists());
    }

    function testCanSaveJpegToNewFile()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::JPEG)->toFile($dir->__toString(),'image');
        $this->assertEquals($path,
                            new T_File_Path($dir->__toString(),'image','jpg'));
        $this->assertTrue($path->exists());
    }

    function testCanSaveReplaceImageFileUsingAtomicSwap()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $target = $dir->__toString().'image.png';
        touch($target);
        $this->assertTrue(filesize($target)<10);
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::PNG)->toFile($dir->__toString(),'image');
        $this->assertEquals($path,
                            new T_File_Path($dir->__toString(),'image','png'));
        clearstatcache();
        $this->assertTrue($path->getSize()>10); /* check its been replaced */
    }

    function testRepeatedPngImageSaveIsPossible()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::PNG);
        /* attempt 1 */
        $path = $im->toFile($dir->__toString(),'image1');
        $this->assertTrue($path->exists());
        /* attempt 2 */
        $path = $im->toFile($dir->__toString(),'image2');
        $this->assertTrue($path->exists());
    }

    function testRepeatedGifImageSaveIsPossible()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::GIF);
        /* attempt 1 */
        $path = $im->toFile($dir->__toString(),'image1');
        $this->assertTrue($path->exists());
        /* attempt 2 */
        $path = $im->toFile($dir->__toString(),'image2');
        $this->assertTrue($path->exists());
    }

    function testRepeatedJpegImageSaveIsPossible()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::JPEG);
        /* attempt 1 */
        $path = $im->toFile($dir->__toString(),'image1');
        $this->assertTrue($path->exists());
        /* attempt 2 */
        $path = $im->toFile($dir->__toString(),'image2');
        $this->assertTrue($path->exists());
    }

    function testSavePngToNewFileFailure()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::PNG);
        $im->handle = false;
        try {
            $path = $im->toFile($dir->__toString(),'image');
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testSaveGifToNewFileFailure()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::PNG);
        $im->handle = false;
        try {
            $path = $im->toFile($dir->__toString(),'image');
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testSaveJpegToNewFileFailure()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::JPEG);
        $im->handle = false;
        try {
            $path = $im->toFile($dir->__toString(),'image');
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testSetQualityFailureWhenNotAPercentInput()
    {
        $im  = new T_Image_Gd(100,200);
        try {
            $im->setQuality(-1);
            $this->fail();
        } catch (T_Exception_Image $e) { }
        try {
            $im->setQuality(101);
            $this->fail();
        } catch (T_Exception_Image $e) { }
    }

    function testPngOutputIsAffectedByQualitySetting()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::PNG);
        /* low quality */
        $path = $im->setQuality(10)
                   ->toFile($dir->__toString(),'image1');
        $low = $path->getSize();
        /* high quality */
        $path = $im->setQuality(100)
                   ->toFile($dir->__toString(),'image2');
        $high = $path->getSize();
        /* low < high */
        $this->assertTrue($low < $high);
    }

    function testJpegOutputIsAffectedByQualitySetting()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::JPEG);
        /* low quality */
        $path = $im->setQuality(10)
                   ->toFile($dir->__toString(),'image1');
        $low = $path->getSize();
        /* high quality */
        $path = $im->setQuality(100)
                   ->toFile($dir->__toString(),'image2');
        $high = $path->getSize();
        /* low < high */
        $this->assertTrue($low < $high);
    }

    function testGifOutputIsNotAffectedByQualitySetting()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $im->convertTo(T_Mime::GIF);
        /* low quality */
        $path = $im->setQuality(10)
                   ->toFile($dir->__toString(),'image1');
        $low = $path->getSize();
        /* high quality */
        $path = $im->setQuality(100)
                   ->toFile($dir->__toString(),'image2');
        $high = $path->getSize();
        /* low < high */
        $this->assertTrue($low === $high);
    }

    function testCanSendPngToBuffer()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::PNG);
        /* save to file to get expected */
        $im->toFile($dir->__toString(),'image');
        $expect = file_get_contents($dir->__toString().'image.png');
        /* get buffer */
        ob_start();
        $im->toBuffer();
        $this->assertSame($expect,ob_get_clean());
    }

    function testCanSendGifToBuffer()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::GIF);
        /* save to file to get expected */
        $im->toFile($dir->__toString(),'image');
        $expect = file_get_contents($dir->__toString().'image.gif');
        /* get buffer */
        ob_start();
        $im->toBuffer();
        $this->assertSame($expect,ob_get_clean());
    }

    function testCanSendJpegToBuffer()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::JPEG);
        /* save to file to get expected */
        $im->toFile($dir->__toString(),'image');
        $expect = file_get_contents($dir->__toString().'image.jpg');
        /* get buffer */
        ob_start();
        $im->toBuffer();
        $this->assertSame($expect,ob_get_clean());
    }

    function testCanRenderPngAsString()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::PNG);
        /* save to file to get expected */
        $im->toFile($dir->__toString(),'image');
        $expect = file_get_contents($dir->__toString().'image.png');
        /* get string */
        $this->assertSame($expect,$im->__toString());
    }

    function testCanRenderJpegAsString()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::JPEG);
        /* save to file to get expected */
        $im->toFile($dir->__toString(),'image');
        $expect = file_get_contents($dir->__toString().'image.jpg');
        /* get string */
        $this->assertSame($expect,$im->__toString());
    }

    function testCanRenderGifAsString()
    {
        $dir = new T_File_Dir(T_CACHE_DIR.'test');
        $im  = new T_Image_Gd(100,200);
        $path = $im->convertTo(T_Mime::GIF);
        /* save to file to get expected */
        $im->toFile($dir->__toString(),'image');
        $expect = file_get_contents($dir->__toString().'image.gif');
        /* get string */
        $this->assertSame($expect,$im->__toString());
    }

    function testResizeDoesNotAffectOriginalImage()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(50,400);
        $this->assertNotSame($im,$new);
        $this->assertSame($new->getWidth(),50);
        $this->assertSame($new->getHeight(),100);
        $this->assertSame($im->getWidth(),100);
        $this->assertSame($im->getHeight(),200);
    }

    function testResizeMaintainsImageTypeInNewImage()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->convertTo(T_Mime::JPEG)->resize(50,400);
        $this->assertSame(T_Mime::JPEG,$new->getMime()->getType());
    }

    function testResizeDoesNotEnlargeImage()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(200,400);
        $this->assertSame($new->getWidth(),100);
        $this->assertSame($new->getHeight(),200);
    }

    function testResizeImageWidthTooBigHeightNot()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(50,400);
        $this->assertSame($new->getWidth(),50);
        $this->assertSame($new->getHeight(),100);
    }

    function testResizeImageHeightTooBigWidthNot()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(200,100);
        $this->assertSame($new->getWidth(),50);
        $this->assertSame($new->getHeight(),100);
    }

    function testResizeBothDimsTooBigWidthConstrained()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(50,175);
        $this->assertSame($new->getWidth(),50);
        $this->assertSame($new->getHeight(),100);
    }

    function testResizeBothDimsTooBigHeightConstrained()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(75,100);
        $this->assertSame($new->getWidth(),50);
        $this->assertSame($new->getHeight(),100);
    }

    function testResizeBothDimsTooBigBothConstrained()
    {
        $im  = new T_Image_Gd(100,200);
        $new = $im->resize(50,100);
        $this->assertSame($new->getWidth(),50);
        $this->assertSame($new->getHeight(),100);
    }

    function tearDown()
    {
        parent::tearDown();
        /* delete all files in .tmp/test directory */
        $tmp = new T_File_Dir(T_CACHE_DIR.'test');
        $tmp->delete();
    }

}
