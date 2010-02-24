<?php
/**
 * Defines the T_Form_Upload class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a file upload input.
 *
 * @package forms
 */
class T_Form_Upload extends T_Form_Element
{

    /**
     * Maximum size of the upload.
     *
     * @var int
     */
    protected $max_size = null;

    /**
     * Sets the maximum size.
     *
     * @param int $max_size  maximum file size limit in bytes
     * @return T_Form_Upload  fluent interface
     */
    function setMaxSize($bytes)
    {
        $this->max_size = (int) $bytes;
        return $this;
    }

    /**
     * Gets the max size of the upload in bytes.
     *
     * @return int  max size of the upload in bytes
     */
    function getMaxSize()
    {
        return $this->max_size;
    }

    /**
     * Whether the field is submitted in the $_FILES superglobal.
     *
     * @param T_Cage_Array $source  source array (POST)
     * @return bool  whether a file is submitted
     */
    function isSubmitted(T_Cage_Array $source)
    {
	if (!($source instanceof T_Cage_Post)) return false;
	$key = $this->getFieldname();
        return ($source->isFile($key) || $source->isFileError($key));
    }

    /**
     * Validate element file upload.
     *
     * @param T_Cage_Array $source  source array (to ignore!)
     * @return T_Form_Element  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $this->reset();
        $this->is_present = $this->isSubmitted($source);
        /* quit if not present, checking that is present if required */
        if (!$this->is_present) {
            if ($this->is_required) {
                $this->error = new T_Form_Error('is missing');
            }
            return $this;
        }
        /* check that no error has occurred during the upload */
        if ($source->isFileError($this->getFieldname())) {
            $err = $source->asFileError($this->getFieldname());
            $this->error = new T_Form_Error($err->getMessage());
            return $this;
        }
        /* get caged value */
        $upload = $source->asFile($this->getFieldname());
        /* now validate input by filtering */
        try {
            foreach ($this->filters as $filter) {
            	$upload = _transform($upload,$filter);
            }
        } catch (T_Exception_Filter $e) {
            $this->error = new T_Form_Error($e->getMessage());
            return $this;
        }
        $this->clean = $upload;
        return $this;
    }

    /**
     * Disable set default.
     *
     * @throws BadFunctionCallException
     */
    function setDefault($value)
    {
        $msg = 'Cannot set a default for file uploads';
        throw new BadFunctionCallException($msg);
    }

}
