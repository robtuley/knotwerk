<?php
class T_Test_File_Unlocked extends T_Unit_Case
{

    protected $fname;
    protected $fclass;

    function setUp()
    {
        $this->fname  = new T_File_Path(T_CACHE_DIR,'testfile','txt');
        $this->fclass = 'T_File_Unlocked';
    }

    protected function getAltFilename()
    {
        return new T_File_Path(T_CACHE_DIR,'testalt','txt');
    }

    function testPortableModeOnly()
    {
        try {
            $file = new $this->fclass($this->fname,'w');
            $this->fail('accepts non-portable open mode.');
        }
        catch (InvalidArgumentException $expected) { }
    }

    function testCloseDestruct()
    {
        // close file using deconstructor
        $file = new $this->fclass($this->fname,'wb');
        $this->assertTrue($file->isOpen());
        unset($file);
        $this->fname->delete();
        // close file using explicit close() method
        $file = new $this->fclass($this->fname,'wb');
        $this->assertTrue($file->isOpen());
        $file->close();
        $this->assertFalse($file->isOpen());
        unset($file);
        $this->fname->delete();
    }

    function testOpenFailureProducesException()
    {
        try {
            $path = new T_File_Path('notadir/nopath','test','txt');
            $file = new $this->fclass($path,'rb');
            $this->fail();
        }
        catch (T_Exception_File $expected) { }
    }

    function testReadWrite()
    {
        $this->assertReadWrite();
        $this->assertReadWrite(1024);
    }

    /**
     * Checks fread and fwrite methods.
     *
     * @param int $writelen  maximum number of bytes to write
     */
    protected function assertReadWrite($writelen=null)
    {
        $content = 'a_string.\with~@@*&%"odd*&^&^%characters';
        // open, write, close, open, read, close, delete
        $file = new $this->fclass($this->fname,'wb');
        if ($writelen===null) {
            $len  = $file->write($content);
        } else {
            $len  = $file->write($content,$writelen);
        }
        unset($file);
        $file = new $this->fclass($this->fname,'rb');
        $data = $file->read($len);
        unset($file);
        $this->assertSame($data,$content);
        $this->fname->delete();
    }

    function testWriteFailure()
    {
        // create file
        $file = new $this->fclass($this->fname,'wb');
        unset($file);
        // reopen file under read mode, and attempt to write
        $file = new $this->fclass($this->fname,'rb');
        try {
            $file->write('some content');
            $this->fail('write does not fail in rb mode.');
        }
        catch (T_Exception_File $expected) { }
        unset($file);
        // delete file
        $this->fname->delete();
    }

    function testWriteZeroLengthString()
    {
        // create file and write zero content
        $file = new $this->fclass($this->fname,'wb');
        $file->write('');
        unset($file);
        // reopen file and test is zero len
        $this->assertSame($this->fname->getSize(),0);
        // delete file
        $this->fname->delete();
    }

    function testReadFailure()
    {
        // create file, and then close handle before attempted read..
        $file = new $this->fclass($this->fname,'wb');
        $file->close();
        try {
            @$file->read(1024);  // suppress warning
            $this->fail('fread does not fail with exception.');
        }
        catch (T_Exception_File $expected) { }
        unset($file);
        // delete file
        $this->fname->delete();
    }

    function testCloseFailure()
    {
        $file = new $this->fclass($this->fname,'wb');
        $file->close();
        try {
            @$file->close();
            $this->fail('close does not fail when already called.');
        }
        catch (T_Exception_File $expected) { }
        $this->fname->delete();
    }

    function testSendToBuffer()
    {
        $content   = 'a_string.\with~@@*&%"odd*&^&^%characters';
        // create file, write content, close
        $file = new $this->fclass($this->fname,'wb');
        $len  = $file->write($content);
        unset($file);
        // open file, and send contents to buffer
        $file = new $this->fclass($this->fname,'rb');
        ob_start();
        $file->sendToBuffer();
        $buffer = ob_get_clean();
        unset($file);
        $this->assertSame($buffer,$content);
        $this->fname->delete();
    }

    function testSendToBufferFailure()
    {
        touch($this->fname->__toString());
        $file = new $this->fclass($this->fname,'rb');
        $file->close();
        try {
            $file->sendToBuffer();
            $this->fail('not exception on sendToBuffer() fail');
        } catch (T_Exception_File $expected) {}
        $this->fname->delete();
    }

    function testOpenRename()
    {
        $content   = 'a_string.\with~@@*&%"odd*&^&^%characters';
        // create file, write content, close and rename
        $file = new $this->fclass($this->fname,'wb');
        $len  = $file->write($content);
        $file->rename($this->getAltFilename());
        unset($file);
        // open read and check content renamed file
        $file = new $this->fclass($this->getAltFilename(),'rb');
        $data = $file->read($len);
        unset($file);
        $this->assertSame($data,$content);
        // delete renamed file
        $this->getAltFilename()->delete();
    }

    function testClosedRename()
    {
        $content   = 'a_string.\with~@@*&%"odd*&^&^%characters';
        // create file, write content, close and rename
        $file = new $this->fclass($this->fname,'wb');
        $len  = $file->write($content);
        $file->close();
        $file->rename($this->getAltFilename());
        unset($file);
        // open read and check content renamed file
        $file = new $this->fclass($this->getAltFilename(),'rb');
        $data = $file->read($len);
        unset($file);
        $this->assertSame($data,$content);
        // delete renamed file
        $this->getAltFilename()->delete();
    }

    function testIteratesOverWindowsDelimitedFile()
    {
        // create file
        $content = "zero\r\none\r\ntwo";
        file_put_contents($this->fname->__toString(),$content);
        // iterate
        $file = new $this->fclass($this->fname,'rb');
        $test = array();
        foreach ($file as $key => $line) {
            $test[$key] = $line;
        }
        unset($file);
        $this->assertSame(array(0=>'zero',1=>'one',2=>'two'),$test);
        // delete file
        $this->fname->delete();
    }

    function testIteratesOverLinuxDelimitedFile()
    {
        // create file
        $content = "zero\none\ntwo";
        file_put_contents($this->fname->__toString(),$content);
        // iterate
        $file = new $this->fclass($this->fname,'rb');
        $test = array();
        foreach ($file as $key => $line) {
            $test[$key] = $line;
        }
        unset($file);
        $this->assertSame(array(0=>'zero',1=>'one',2=>'two'),$test);
        // delete file
        $this->fname->delete();
    }

    function testIteratesOverMacDelimitedFile()
    {
        // create file
        $content = "zero\rone\rtwo";
        file_put_contents($this->fname->__toString(),$content);
        // iterate
        $file = new $this->fclass($this->fname,'rb');
        $test = array();
        foreach ($file as $key => $line) {
            $test[$key] = $line;
        }
        unset($file);
        $this->assertSame(array(0=>'zero',1=>'one',2=>'two'),$test);
        // delete file
        $this->fname->delete();
    }

    function testIteratesSingleLine()
    {
        // create file
        $content = "zero";
        file_put_contents($this->fname->__toString(),$content);
        // iterate
        $file = new $this->fclass($this->fname,'rb');
        $test = array();
        foreach ($file as $key => $line) {
            $test[$key] = $line;
        }
        unset($file);
        $this->assertSame(array(0=>'zero'),$test);
        // delete file
        $this->fname->delete();
    }

    function testLineIterationIsRepeatable()
    {
        // create file
        $content = "zero\none\ntwo";
        file_put_contents($this->fname->__toString(),$content);
        // iterate
        $file = new $this->fclass($this->fname,'rb');
        $test = array();
        foreach ($file as $key => $line) { }
        foreach ($file as $key => $line) {
            $test[$key] = $line;
        }
        unset($file);
        $this->assertSame(array(0=>'zero',1=>'one',2=>'two'),$test);
        // delete file
        $this->fname->delete();
    }

}
