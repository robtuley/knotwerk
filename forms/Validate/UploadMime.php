<?php
/**
 * Defines the T_Validate_UploadMime class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that an uploaded file is of a certain type.
 *
 * @package forms
 */
class T_Validate_UploadMime extends T_Filter_Skeleton
{

    /**
     * Permitted MIME types.
     *
     * @var array
     */
    protected $mime;

    /**
     * Create filter.
     *
     * @param array $exts  array of MIME extensions
     * @param function $filter  prior filter in chain
     */
    function __construct(array $exts,$filter=null)
    {
        parent::__construct($filter);
        $this->mime = array();
        foreach ($exts as $e) {
            $mime = new T_File_Mime($e);
            $this->mime[$e] = $mime->getType();
        }
    }

    /**
     * Checks uploaded file is one of the mime types.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $type = $value->getMime()->getType();
        // check the file extension
        if (!in_array($type,$this->mime)) {
            $msg = "is not a permitted file: valid types are ".
                                  implode(', ',array_keys($this->mime)).' only';
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}