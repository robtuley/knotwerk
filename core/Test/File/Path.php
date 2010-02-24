<?php
class T_Test_File_Path extends T_Unit_Case
{

    protected function getPathObject($dir,$file,$ext)
    {
        return new T_File_Path($dir,$file,$ext);
    }

    function testNullIsConvertedToCurrentDirectory()
    {
        $dir = $this->getPathObject(null,'file','txt');
        $this->assertSame('.'.DIRECTORY_SEPARATOR,$dir->getDirName());
    }

    function testForwardSlashIsAddedToDirectoryPath()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,$dir->getDirName());
    }

    function testForwardSlashIsNormalisedInPath()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test/','file','txt');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,$dir->getDirName());
    }

    function testBackwardSlashIsNormalisedInPath()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test\\','file','txt');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,$dir->getDirName());
    }

    function testSetAndGetDirAndFilenameWithExtension()
    {
        $path = $this->getPathObject('some/path','file','txt');
        $str = 'some/path'.DIRECTORY_SEPARATOR.'file.txt';
        $this->assertSame($str,$path->__toString());
        $this->assertSame('some/path'.DIRECTORY_SEPARATOR,$path->getDirName());
        $this->assertSame('file',$path->getFilename());
        $this->assertSame('txt',$path->getMime()->getExt());
    }

    function testSetAndGetFilenameWithExtensionInCurrentDir()
    {
        $path = $this->getPathObject(null,'file','txt');
        $str = '.'.DIRECTORY_SEPARATOR.'file.txt';
        $this->assertSame($str,$path->__toString());
        $this->assertSame('.'.DIRECTORY_SEPARATOR,$path->getDirName());
        $this->assertSame('file',$path->getFilename());
        $this->assertSame('txt',$path->getMime()->getExt());
    }

    function testSetAndGetDirAndFilenameWithNoExtension()
    {
        $path = $this->getPathObject('some/path','file',null);
        $str = 'some/path'.DIRECTORY_SEPARATOR.'file';
        $this->assertSame($str,$path->__toString());
        $this->assertSame('some/path'.DIRECTORY_SEPARATOR,$path->getDirName());
        $this->assertSame('file',$path->getFilename());
        $this->assertSame(null,$path->getMime()->getExt());
    }

    function testSetAndGetHiddenFile()
    {
        $path = $this->getPathObject('some/path','.hidden',null);
        $str = 'some/path'.DIRECTORY_SEPARATOR.'.hidden';
        $this->assertSame($str,$path->__toString());
        $this->assertSame('some/path'.DIRECTORY_SEPARATOR,$path->getDirName());
        $this->assertSame('.hidden',$path->getFilename());
        $this->assertSame(null,$path->getMime()->getExt());
    }

    function testFilenameCanBeZeroLength()
    {
        // e.g. /some/path/.htaccess
        $path = $this->getPathObject('some/path',null,'htaccess');
        $this->assertSame('',$path->getFilename());
    }

    function testUnrecognisedMimeTypeExtsAreSupported()
    {
        $path = $this->getPathObject('some/path','file','notext');
        $this->assertSame($path->getMime()->getType(),T_Mime::BINARY);
    }

    function testExistsMethodWhenNoFile()
    {
        $path = $this->getPathObject(T_CACHE_DIR,'file','txt');
        $this->assertFalse($path->exists());
    }

    function testExistsMethodWhenIsFile()
    {
        mkdir(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test/file.txt');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        $this->assertTrue($path->exists());
    }

    function testCanDeleteExistingFile()
    {
        mkdir(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test/file.txt');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        $this->assertTrue($path->exists());
        $test = $path->delete();
        $this->assertFalse($path->exists());
        $this->assertSame($test,$path,'Fluent interface');
    }

    function testDeleteFailWhenThereIsNoFile()
    {
        $path = $this->getPathObject(T_CACHE_DIR,'file','txt');
        try {
            $path->delete();
            $this->fail();
        } catch (T_Exception_File $e) { }
    }

    /**
     * Gets a set of differently sized files.
     *
     * @return array  array of T_File_Path objects
     */
    function getMultiSizePaths()
    {
        $dir = T_ROOT_DIR.'core/Test/_sample/';
        $multisize = array('byte' => new T_File_Path($dir,'byte_size','txt'),
                           'kb' => new T_File_Path($dir,'kilobyte_size','txt'));
        return $multisize;
    }

    function testSizeMethodGetsFileSize()
    {
        $multisize = $this->getMultiSizePaths();
        foreach ($multisize as $path) {
            $this->assertSame($path->getSize(),filesize($path->__toString()));
        }
    }

    function assertHumanSize($prec)
    {
        $multisize = $this->getMultiSizePaths();
        // test byte size file
        $path = $multisize['byte'];
        $exp = filesize($path->__toString()).'B';
        $this->assertSame($path->getHumanSize($prec),$exp);
        // test KB sized..
        $path = $multisize['kb'];
        $exp = round(filesize($path->__toString())/1024,$prec).'KB';
        $this->assertSame($path->getHumanSize($prec),$exp);
    }

    function testHumanSizeWithVariousPrecision()
    {
        $this->assertHumanSize(0);
        $this->assertHumanSize(1);
        $this->assertHumanSize(2);
    }

    function testGetLastModifiedTime()
    {
        $files = $this->getMultiSizePaths();
        foreach ($files as $file) {
            $expect = filemtime($file->__toString());
            $this->assertSame($file->getLastModified(),$expect);
        }
    }

    // T_File_Path::rename()

    function testRenameOverwritesExistingOnUnixSystem()
    {
        if (T_WINDOWS) {
            $this->skip('Only applicable to UNIX systems');
        }
        mkdir(T_CACHE_DIR.'test');
        if (!$fp=fopen(T_CACHE_DIR.'test/source.txt','wb')) {
            throw new Exception('could not create source');
        }
        fwrite($fp,'content');
        fclose($fp);
        touch(T_CACHE_DIR.'test/target.txt');
        if (!rename(T_CACHE_DIR.'test/source.txt',T_CACHE_DIR.'test/target.txt')) {
            throw new Exception('rename failed');
        }
        if (!$fp=fopen(T_CACHE_DIR.'test/target.txt','rb')) {
            throw new Exception('could not open target');
        }
        $this->assertSame('content',fread($fp,4096));
        fclose($fp);
    }

    function testRenameExistingFileToNonExistingLocation()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        touch($path->__toString());
        $alt = $this->getPathObject(T_CACHE_DIR.'test','alternative','txt');
        $test = $path->rename($alt);
        clearstatcache();
        $this->assertTrue(is_file(T_CACHE_DIR.'test/alternative.txt'));
        $this->assertFalse(is_file(T_CACHE_DIR.'test/file.txt'));
        $this->assertSame($test,$path,'fluent interface');
    }

    function testRenameExistingFileToExistingLocation()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','source','txt');
        /* create some content in file */
        touch($path->__toString());
        $file = new T_File_Unlocked($path,'wb');
        $len = $file->write('content');
        $file->close();
        unset($file);
        /* create existing alternative */
        $alt = $this->getPathObject(T_CACHE_DIR.'test','target','txt');
        touch($alt->__toString());
        $path->rename($alt);
        clearstatcache();
        /* assert new file in new location */
        $this->assertTrue(is_file(T_CACHE_DIR.'test/target.txt'));
        $this->assertFalse(is_file(T_CACHE_DIR.'test/source.txt'));
        $file = new T_File_Unlocked($path,'rb');
        $this->assertSame('content',$file->read($len));
        $file->close();
        unset($file);
    }

    function testRenameChangesDetailsToNewPath()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        touch($path->__toString());
        $alt = $this->getPathObject(T_CACHE_DIR.'test','alternative','txt');
        $path->rename($alt);
        $this->assertEquals($alt,$path);
    }

    function testRenameFailureResultsInAFileException()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        touch($path->__toString());
        $alt = $this->getPathObject(T_CACHE_DIR.'not/a/dir','alternative','txt');
        try {
            $path->rename($alt);
            $this->fail();
        } catch (T_Exception_File $e) { }
    }

    // T_File_Path::copyTo()

    function testCopyFileToNonExistingLocation()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        touch($path->__toString());
        $alt = $this->getPathObject(T_CACHE_DIR.'test','alternative','txt');
        $test = $path->copyTo($alt);
        clearstatcache();
        $this->assertTrue(is_file(T_CACHE_DIR.'test/alternative.txt'));
        $this->assertTrue(is_file(T_CACHE_DIR.'test/file.txt'));
        $this->assertSame($test,$path,'fluent interface');
    }

    function testCopyFileToExistingLocation()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','source','txt');
        /* create some content in file */
        touch($path->__toString());
        $file = new T_File_Unlocked($path,'wb');
        $len = $file->write('content');
        $file->close();
        unset($file);
        /* create existing alternative */
        $alt = $this->getPathObject(T_CACHE_DIR.'test','target','txt');
        touch($alt->__toString());
        $path->copyTo($alt);
        clearstatcache();
        /* assert new file in new location */
        $this->assertTrue(is_file(T_CACHE_DIR.'test/target.txt'));
        $this->assertTrue(is_file(T_CACHE_DIR.'test/source.txt'));
        $file = new T_File_Unlocked($path,'rb');
        $this->assertSame('content',$file->read($len));
        $file->close();
        unset($file);
    }

    function testCopyNonExistingFileFailureResultsInAFileException()
    {
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        $alt = $this->getPathObject(T_CACHE_DIR.'not/a/dir','alternative','txt');
        try {
            $path->copyTo($alt);
            $this->fail();
        } catch (T_Exception_File $e) { }
    }

    function testCopyExistingFileFailureResultsInAFileException()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        touch($path->__toString());
        $alt = $this->getPathObject(T_CACHE_DIR.'not/a/dir','alternative','txt');
        try {
            $path->copyTo($alt);
            $this->fail();
        } catch (T_Exception_File $e) { }
    }

    // T_File_Path::getContent()

    function testGetContentOfNonExistingFileReturnsNull()
    {
        $path = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        $this->assertSame(null,$path->getContent());
    }

    function testCanGetContentOfExistingFile()
    {
        mkdir(T_CACHE_DIR.'test');
        $path = $this->getPathObject(T_CACHE_DIR.'test','source','txt');
        /* create some content in file */
        touch($path->__toString());
        $file = new T_File_Unlocked($path,'wb');
        $len = $file->write('content');
        $file->close();
        unset($file);
        /* retrieve content */
        $this->assertSame($path->getContent(),'content');
    }

    function testCanFilterGetContent()
    {
        mkdir(T_CACHE_DIR.'test');
        $f = new T_Test_Filter_Suffix();
        $path = $this->getPathObject(T_CACHE_DIR.'test','source','txt');
        touch($path->__toString());
        $file = new T_File_Unlocked($path,'wb');
        $len = $file->write('content');
        $file->close();
        unset($file);
        $this->assertSame($path->getContent($f),$f->transform('content'));
    }

    // T_File_Path::__clone()

    function testClonedFilepathContainsClonedMimeObject()
    {
        $path1 = $this->getPathObject(T_CACHE_DIR.'test','file','txt');
        $path2 = clone $path1;
        $this->assertEquals($path1,$path2);
        $this->assertNotSame($path1,$path2);
        $this->assertNotSame($path1->getMime(),$path2->getMime());
    }

    /**
     * Remove test files and directories.
     */
    function tearDown()
    {
        parent::tearDown();
        /* delete all files in tmp directory */
        $tmp = new T_File_Dir(T_CACHE_DIR.'test');
        $tmp->delete();
        /* clear file stat cache */
        clearstatcache();
    }

}
