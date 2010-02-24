<?php
/**
 * Unit test cases for the T_File_View class.
 *
 * @package viewTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**Units tests for the T_File_View class.
 *
 * @package viewTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_File_View extends T_Unit_Case
{

    function testClassImplementsView()
    {
        mkdir(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test/common.css');
        $view = new T_File_View(T_CACHE_DIR.'test','common','css');
        $this->assertTrue($view instanceof T_View);
    }

    function testClassImplementsFilePath()
    {
        mkdir(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test/common.css');
        $view = new T_File_View(T_CACHE_DIR.'test','common','css');
        $this->assertTrue($view instanceof T_File_Path);
    }

    function testFailsIfFileDoesNotExist()
    {
        try {
            $view = new T_File_View(T_CACHE_DIR.'test','common','css');
            $this->fail();
        } catch (T_Exception_File $e) { }
    }

    function testCanExtractMimeType()
    {
        mkdir(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test/common.css');
        $view = new T_File_View(T_CACHE_DIR.'test','common','css');
        $this->assertEquals($view->getMime(),new T_File_Mime('css'));
    }

    function testToStringOutputsFileContents()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = new T_File_Path(T_CACHE_DIR.'test','common','css');
        $file = new T_File_Unlocked($path,'wb');
        $file->write('somecontent');
        $file->close();
        $view = new T_File_View(T_CACHE_DIR.'test','common','css');
        $expect = file_get_contents($path->__toString());
        $this->assertSame($expect,$view->__toString());
    }

    function testToBufferOutputsFileContentsToBuffer()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = new T_File_Path(T_CACHE_DIR.'test','common','css');
        $file = new T_File_Unlocked($path,'wb');
        $file->write('somecontent');
        $file->close();
        $view = new T_File_View(T_CACHE_DIR.'test','common','css');
        $expect = file_get_contents($path->__toString());
        ob_start();
        $test = $view->toBuffer();
        $this->assertSame($view,$test,'fluent interface');
        $test = ob_get_clean();
        $this->assertSame($expect,$test);
    }

    function testToBufferFailure()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = new T_File_Path(T_CACHE_DIR.'test','common','css');
        $file = new T_File_Unlocked($path,'wb');
        $file->write('somecontent');
        $file->close();
        $view = new T_File_View(T_CACHE_DIR.'test','common','css');
        $view->delete();
        try {
            $view->toBuffer();
            $this->fail();
        } catch (T_Exception_File $e) { }
    }

    function tearDown()
    {
        parent::tearDown();
        $tmp = new T_File_Dir(T_CACHE_DIR.'test');
        $tmp->delete();
    }

}
