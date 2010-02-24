<?php
/**
 * Unit test cases for the T_File_Uploaded class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_File_Uploaded test cases.
 *
 * @package formTests
 */
class T_Test_File_Uploaded extends T_Unit_Case
{

    function testSizeSetInConstructor()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.txt');
        $this->assertSame(1024,$file->getSize());
    }

    function testOriginalFilenameIsStored()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.txt');
        $this->assertSame('file',$file->getFilename()->uncage());
    }

    function testOriginalFilenameWithMultipleDots()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.extra.txt');
        $this->assertSame('file.extra',$file->getFilename()->uncage());
    }

    function testOriginalFilenameWithNoLength()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'.txt');
        $this->assertSame(null,$file->getFilename()->uncage());
    }

    function testExtensionIsBasedOnOriginalFileExtension()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.txt');
        $this->assertEquals(new T_File_Mime('txt'),$file->getMime());
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.jpg');
        $this->assertEquals(new T_File_Mime('jpg'),$file->getMime());
    }

    function testUnrecognizedExtensionSetAsBinaryStream()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.notanext');
        $this->assertSame(T_Mime::BINARY,$file->getMime()->getType());
    }

    function testPathSetInConstructor()
    {
        $file = new T_File_Uploaded('some/tmp/file',1024,'file.notanext');
        $this->assertSame('some/tmp/file',$file->__toString());
    }

}