<?php
class T_Test_Cage_Post extends T_Unit_Case
{

    function testActsAsAStandardCageOnFirstArg()
    {
        $expect = array('a','b'=>'c',4);
        $cage = new T_Cage_Post($expect,array());
        $this->assertSame($expect,$cage->uncage());
    }

    /**
     * Only some tests are possible due to the fact that the paths use the
     * function is_uploaded_file to validate filenames.
     */

    function testIsFileAndIsErrorReturnsFalseWhenNotPresent()
    {
        $test = new T_Cage_Post(array('key'=>'value'),array());
        $this->assertFalse($test->isFile('key'));
        $this->assertFalse($test->isFileError('key'));
    }

    function testFileUploadErrorAsNotUploadedFile()
    {
        $files = array( 'field' => array( 'name' => 'file.txt',
                                          'size' => 1000,
                                          'tmp_name' => 'some/tmp/path',
                                          'error' => UPLOAD_ERR_OK      )  );
        $test = new T_Cage_Post(array(),$files);
        $this->assertFalse($test->isFile('field'));
        $this->assertTrue($test->isFileError('field'));
        $e = $test->asFileError('field');
        $this->assertTrue($e instanceof T_Exception_UploadedFile);
    }

    function testFileUploadErrorAsFlaggedErrorStatus()
    {
        $files = array( 'field' => array( 'name' => 'file.txt',
                                          'size' => 1000,
                                          'tmp_name' => 'some/tmp/path',
                                          'error' => UPLOAD_ERR_FORM_SIZE )  );
        $test = new T_Cage_Post(array(),$files);
        $this->assertTrue($test->isFileError('field'));
        $this->assertFalse($test->isFile('field'));
        $e = $test->asFileError('field');
        $this->assertTrue($e instanceof T_Exception_UploadedFile);
    }

    function testFileUploadErrorAsSizeIsZero()
    {
        $files = array( 'field' => array( 'name' => 'file.txt',
                                          'size' => 0,
                                          'tmp_name' => 'some/tmp/path',
                                          'error' => UPLOAD_ERR_OK      )  );
        $test = new T_Cage_Post(array(),$files);
        $this->assertTrue($test->isFileError('field'));
        $this->assertFalse($test->isFile('field'));
        $e = $test->asFileError('field');
        $this->assertTrue($e instanceof T_Exception_UploadedFile);
    }

    function testFileUploadErrorAsZeroLengthTempName()
    {
        $files = array( 'field' => array( 'name' => 'file.txt',
                                          'size' => 1000,
                                          'tmp_name' => null,
                                          'error' => UPLOAD_ERR_OK      )  );
        $test = new T_Cage_Post(array(),$files);
        $this->assertTrue($test->isFileError('field'));
        $this->assertFalse($test->isFile('field'));
        $e = $test->asFileError('field');
        $this->assertTrue($e instanceof T_Exception_UploadedFile);
    }

    function testExceptionThrownInvalidFileAccessed()
    {
        $test = new T_Cage_Post(array(),array());
        try {
            $test->asFile('field');
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testExceptionThrownNotPresentErrorAccessed()
    {
        $test = new T_Cage_Post(array(),array());
        try {
            $test->asFileError('field');
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

}
