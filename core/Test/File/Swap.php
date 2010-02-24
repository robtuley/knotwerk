<?php
class T_Test_File_Swap extends T_Test_File_Unlocked
{

    function setUp()
    {
        parent::setUp();
        $this->fclass = 'T_File_Swap';
    }

    function testReadorWriteModeOnly()
    {
        $modes = array('xb','xb+','ab','ab+','rb+','wb+');
        foreach ($modes as $value) {
            try {
                $file = new $this->fclass($this->fname,$value);
                $this->fail("accepts $value open mode.");
            }
            catch (InvalidArgumentException $expected) { }
        }
    }

    function testIntermediateReadNewFile()
    {
        $file = new $this->fclass($this->fname,'wb');
        $file->write('some content');
        // at this point, the file should not exist yet (not closed the file)
        $this->assertFalse($this->fname->exists());
        unset($file); // [or $file->close()]
        // file closed, therefore the write is committed.
        $this->assertTrue($this->fname->exists());
        unlink($this->fname->__toString());
    }

    function testIntermediateReadOldFile()
    {
        $ocontent = 'oldcontent';
        $ncontent = 'newcontent';

        // existing file contains old content.
        $ofile = new $this->fclass($this->fname,'wb');
        $olen  = $ofile->write($ocontent);
        unset($ofile);

        // a NEW swap write file is created, start writing new content
        $nfile = new $this->fclass($this->fname,'wb');
        $nlen  = $nfile->write($ncontent);

        // ... meanwhile ... old file can still be opened and read ...
        $rfile = new $this->fclass($this->fname,'rb');
        $this->assertSame($rfile->read($olen),$ocontent);
        unset($rfile); // must close to prevent deadlock

        // ... until ... new file is committed when the new data becomes
        // available instead
        unset($nfile);
        $rfile = new $this->fclass($this->fname,'rb');
        $this->assertSame($rfile->read($nlen),$ncontent);
        unset($rfile);

        // clean up file
        unlink($this->fname->__toString());
    }
}
