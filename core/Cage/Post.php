<?php
/**
 * Defines the T_Cage_Post class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates POST data and uploaded files.
 *
 * @package core
 */
class T_Cage_Post extends T_Cage_Array
{

    /**
     * Array of file data.
     *
     * @var array
     */
    protected $files = array();

    /**
     * Encapsulate $_FILES superglobal.
     *
     * @param array $files
     */
    function __construct(array $data,array $files)
    {
	parent::__construct($data);
        foreach ($files as $name => $data) {
            /* check whether an upload attempt was made */
            if ($data['error']==UPLOAD_ERR_NO_FILE) {
                continue;  /* skip */
            }
            $msg = 'An error occurred uploading the file';
            /* check uploaded with no error */
            if ($data['error'] !== UPLOAD_ERR_OK) {
        	$this->files[$name] = new T_Exception_UploadedFile($msg);
        	continue;
            }
            /* check that size > 0 and tmp_name is non-zero */
            if ($data['size']<1 || strlen($data['tmp_name'])<1) {
        	$this->files[$name] = new T_Exception_UploadedFile($msg);
        	continue;
            }
            /* check is uploaded file */
            if (!is_uploaded_file($data['tmp_name'])) {
        	$this->files[$name] = new T_Exception_UploadedFile($msg);
        	continue;
            }
            /* valid file */
            $this->files[$name] = new T_File_Uploaded($data['tmp_name'],
        	                                     $data['size'],
        	                                     $data['name']   );
        }
    }

    /**
     * Whether a key is a valid uploaded file.
     *
     * @param string $key  fieldname
     * @return bool
     */
    function isFile($key)
    {
        return isset($this->files[$key]) &&
               ($this->files[$key] instanceof T_File_Uploaded);
    }

    /**
     * Whether a key had an error while uploading a file.
     *
     * @param string $key  fieldname
     * @return bool
     */
    function isFileError($key)
    {
        return isset($this->files[$key]) &&
               ($this->files[$key] instanceof T_Exception_UploadedFile);
    }

    /**
     * Retrieve a valid file upload.
     *
     * @param string $key  fieldname
     * @return T_File_Uploaded  uploaded file
     */
    function asFile($key)
    {
        if (!$this->isFile($key)) {
            throw new T_Exception_Cage("$key is not a valid file");
        }
        return $this->files[$key];
    }

    /**
     * Retrieve the details of an upload error.
     *
     * @param string $key  fieldname
     * @return T_Exception_UploadedFile  upload error
     */
    function asFileError($key)
    {
        if (!$this->isFileError($key)) {
            throw new T_Exception_Cage("$key is not a upload error");
        }
        return $this->files[$key];
    }

}
