<?php
class T_Test_Code_Files extends T_Unit_Case
{

    function setUp()
    {
        if (!is_dir(T_CACHE_DIR)) mkdir(T_CACHE_DIR);
    }

    function testExceptionThrownIfOneOfFilesDoNotExist()
    {
        try {
            $files = new T_Code_Files(T_CACHE_DIR,array('notexist'),'.css');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testGetPathsReturnsArrayOfFullFilePaths()
    {
        $names = array('test1','test2');
        $ext = '.js';
        foreach ($names as $fn) {
            $p = T_CACHE_DIR.$fn.$ext;
            $expect[] = $p;
            touch($p);
        }

        $files = new T_Code_Files(T_CACHE_DIR,$names,$ext);
        $this->assertSame($expect,$files->getPaths());

        foreach ($expect as $p) @unlink($p);
    }

    function testGetVersionReturnsRepeatableHashIndepedentOfOrder()
    {
        $names = array('test1','test2');
        $ext = '.js';
        foreach ($names as $fn) {
            $p = T_CACHE_DIR.$fn.$ext;
            $expect[] = $p;
            touch($p);
        }

        $files1 = new T_Code_Files(T_CACHE_DIR,$names,$ext);
        $files2 = new T_Code_Files(T_CACHE_DIR,array_reverse($names),$ext);
        $this->assertTrue(strlen($files1->getVersion())>0);
        $this->assertSame($files1->getVersion(),$files1->getVersion());
        $this->assertSame($files2->getVersion(),$files1->getVersion());

        foreach ($expect as $p) @unlink($p);
    }

    function testGetVersionDependsOnFilesInGroup()
    {
        $names = array('test1','test2');
        $ext = '.js';
        foreach ($names as $fn) {
            $p = T_CACHE_DIR.$fn.$ext;
            $expect[] = $p;
            touch($p);
        }

        $files1 = new T_Code_Files(T_CACHE_DIR,$names,$ext);
        $files2 = new T_Code_Files(T_CACHE_DIR,array(_first($names)),$ext);
        $this->assertNotEquals($files2->getVersion(),$files1->getVersion());

        foreach ($expect as $p) @unlink($p);
    }

    function testGetVersionDependsOnLastModificationTimeOfFile()
    {
        $names = array('test1','test2');
        $ext = '.js';
        foreach ($names as $fn) {
            $p = T_CACHE_DIR.$fn.$ext;
            $expect[] = $p;
            touch($p);
        }

        $files = new T_Code_Files(T_CACHE_DIR,$names,$ext);
        $version = $files->getVersion();
        sleep(1);
        touch(_end($expect)); clearstatcache();
        $this->assertNotEquals($files->getVersion(),$version);

        foreach ($expect as $p) @unlink($p);
    }

    function testCanSwitchIsCompleteOnFromDefaultOff()
    {
        $names = array('test1','test2');
        $ext = '.js';
        foreach ($names as $fn) {
            $p = T_CACHE_DIR.$fn.$ext;
            $expect[] = $p;
            touch($p);
        }

        $files = new T_Code_Files(T_CACHE_DIR,$names,$ext);
        $this->assertFalse($files->isComplete());
        $this->assertSame($files,$files->setComplete());
        $this->assertTrue($files->isComplete());

        foreach ($expect as $p) @unlink($p);
    }








}
