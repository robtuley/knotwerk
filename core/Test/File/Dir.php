<?php
/**
 * Unit test cases for the T_File_Dir class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_File_Dir test cases.
 *
 * @package coreTests
 */
class T_Test_File_Dir extends T_Unit_Case
{

    /**
     * Gets directory object.
     *
     * @param string $dir  string directory
     * @return  T_File_Dir  directory object
     */
    protected function getPathObject($dir)
    {
        return new T_File_Dir($dir);
    }

    function testNullIsConvertedToCurrentDirectory()
    {
        $dir = $this->getPathObject(null);
        $this->assertSame('.'.DIRECTORY_SEPARATOR,$dir->__toString());
    }

    function testForwardSlashIsAddedToDirectoryPath()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,$dir->__toString());
    }

    function testForwardSlashIsNormalisedInPath()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test/');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,$dir->__toString());
    }

    function testBackwardSlashIsNormalisedInPath()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test\\');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,$dir->__toString());
    }

    function testFailOnFilePath()
    {
        mkdir(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test/file');
        try {
            $this->getPathObject(T_CACHE_DIR.'test/file');
            $this->fail('accepts existing file path');
        } catch (T_Exception_File $e) {}
    }

    function testCreateDir()
    {
        $this->assertFalse(is_dir(T_CACHE_DIR.'test'));
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $this->assertTrue(is_dir(T_CACHE_DIR.'test'));
    }

    function testCreateDirRecursively()
    {
        $this->assertFalse(is_dir(T_CACHE_DIR.'test'));
        $dir = $this->getPathObject(T_CACHE_DIR.'test/nest/');
        $this->assertTrue(is_dir(T_CACHE_DIR.'test'));
        $this->assertTrue(is_dir(T_CACHE_DIR.'test/nest'));
    }

    function testOpenExistingDir()
    {
        mkdir(T_CACHE_DIR.'test');
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $this->assertTrue(is_dir(T_CACHE_DIR.'test'));
    }

    function testExistingSubDir()
    {
        mkdir(T_CACHE_DIR.'test');
        $dir = $this->getPathObject(T_CACHE_DIR);
        $subdir = $dir->getChildDir('test');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,
                          $subdir->__toString()  );
    }

    function testNewSubDir()
    {
        $this->assertFalse(is_dir(T_CACHE_DIR.'test'));
        $dir = $this->getPathObject(T_CACHE_DIR);
        $subdir = $dir->getChildDir('test');
        $this->assertSame(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR,
                          $subdir->__toString()  );
        $this->assertTrue(is_dir(T_CACHE_DIR.'test'));
    }

    function testEmptyDirectoryIteration()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        foreach ($dir as $file) {
        	$this->fail();
        }
    }

    function testSingleFileIteration()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        touch(T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR.'file.txt');
        $expect = new T_File_Path(T_CACHE_DIR.'test','file','txt');
        $i = 0;
        foreach ($dir as $key => $file) {
            $this->assertSame($i,$key);
            $this->assertEquals($expect,$file);
            $i++;
        }
        $this->assertSame(1,$i);
    }

    function testMultiDirIteration()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $subdirs[] = $dir->getChildDir('subdir1')->__toString();
        $subdirs[] = $dir->getChildDir('subdir2')->__toString();
        $i = 0;
        foreach ($dir as $key => $sub) {
            $this->assertSame($i,$key);
            $this->assertTrue($sub instanceof T_File_Dir);
            $paths[] = $sub->__toString();
            $i++;
        }
        $this->assertSame(2,$i);
        foreach ($paths as $value) {
            $this->assertTrue(in_array($value,$subdirs));
        }
    }

    function testFileDirIteration()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $expect[] = $dir->getChildDir('subdir')->__toString();
        touch(T_CACHE_DIR.'test/testfile.txt');
        $expect[] = T_CACHE_DIR.'test'.DIRECTORY_SEPARATOR.'testfile.txt';
        $i = 0;
        foreach ($dir as $key => $file) {
            $this->assertSame($i,$key);
            $paths[] = $file->__toString();
            $i++;
        }
        $this->assertSame(2,$i);
        foreach ($paths as $value) {
            $this->assertTrue(in_array($value,$expect));
        }
    }

    function testCanRemoveEmptyDirectory()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $dir->delete();
        $this->assertFalse(is_dir(T_CACHE_DIR.'test'));
    }

    function testCanRemoveNonEmptyDirectory()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $dir->getChildDir('subdir1');
        touch(T_CACHE_DIR.'test/file.txt');
        $dir->delete();
        $this->assertFalse(is_dir(T_CACHE_DIR.'test'));
    }

    function testCanRemoveNestedNonEmptyDirectoryRecursively()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $dir->getChildDir('subdir1');
        touch(T_CACHE_DIR.'test/subdir1/file.txt');
        $dir->delete();
        $this->assertFalse(is_dir(T_CACHE_DIR.'test'));
    }

    function testDeleteFail()
    {
        $dir = $this->getPathObject(T_CACHE_DIR.'test');
        $dir->delete();
        try {
            $dir->delete();
            $this->fail();
        } catch(T_Exception_File $e) {}
    }

    function tearDown()
    {
        $tmp = new T_File_Dir(T_CACHE_DIR.'test');
        $tmp->delete();
    }

}
