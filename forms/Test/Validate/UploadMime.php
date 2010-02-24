<?php
/**
 * Unit test cases for the T_Validate_UploadMime class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_UploadMime unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_UploadMime extends T_Test_Filter_SkeletonHarness
{

    function testFilterAcceptsNothingWhenPassedNoMimeTypes()
    {
        $filter = new T_Validate_UploadMime(array());
        $invalid = array( new T_File_Path('dir','file','pdf'),
                          new T_File_Path('dir','file','txt') );
        foreach ($invalid as $file) {
            try {
                $filter->transform($file);
                $this->fail();
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testAcceptsFileWithSingleMimeType()
    {
        $filter = new T_Validate_UploadMime(array('pdf'));
        $file = new T_File_Path('dir','file','pdf');
        $this->assertSame($file,$filter->transform($file));
    }

    function testChecksMimeTypeNotExtension()
    {
        $filter = new T_Validate_UploadMime(array('jpg'));
        $valid = array( new T_File_Path('dir','file','jpg'),
                        new T_File_Path('dir','file','JPG'),
                        new T_File_Path('dir','file','jpeg') );
        foreach ($valid as $file) {
            $this->assertSame($file,$filter->transform($file));
        }
    }

    function testFilterAcceptsOnlySingleMimeType()
    {
        $filter = new T_Validate_UploadMime(array('jpg'));
        $invalid = array( new T_File_Path('dir','file','pdf'),
                          new T_File_Path('dir','file','txt') );
        foreach ($invalid as $file) {
            try {
                $filter->transform($file);
                $this->fail();
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testFilterCanAcceptMultipleMimeTypes()
    {
        $filter = new T_Validate_UploadMime(array('jpg','pdf','txt'));
        $valid = array( new T_File_Path('dir','file','jpeg'),
                        new T_File_Path('dir','file','PDF'),
                        new T_File_Path('dir','file','txt') );
        foreach ($valid as $file) {
            $this->assertSame($file,$filter->transform($file));
        }
    }

    function testFilterRejectsMimeTypesOutsideSet()
    {
        $filter = new T_Validate_UploadMime(array('jpg','gif'));
        $invalid = array( new T_File_Path('dir','file','pdf'),
                          new T_File_Path('dir','file','txt') );
        foreach ($invalid as $file) {
            try {
                $filter->transform($file);
                $this->fail();
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testCanPipePriorFilter()
    {
        $pipe = new T_Validate_UploadMime(array('jpg'));
        $filter = new T_Validate_UploadMime(array('pdf'),$pipe);
        $file = new T_File_Path('dir','file','pdf');
        try {
            $filter->transform($file);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

}