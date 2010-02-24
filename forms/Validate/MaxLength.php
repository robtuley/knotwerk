<?php
/**
 * Contains the T_Validate_MaxLength class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This checks data is not over a maximum string length.
 *
 * @package forms
 */
class T_Validate_MaxLength extends T_Filter_Skeleton
{

    /**
     * Maximum length.
     *
     * @var int
     */
    protected $max_len;

    /**
     * Setup maximum length.
     *
     * @param int $max_len  maximum length
     * @param function $filter  prior filter object
     */
    function __construct($max_len,$filter=null)
    {
        $this->max_len = (int) $max_len;
        parent::__construct($filter);
    }

    /**
     * Checks that the data is not over a maximum length.
     *
     * @param string $value  data to filter
     * @return string  string under a maximum length
     * @throws T_Exception_Filter  when data > max length
     */
    protected function doTransform($value)
    {
        if (strlen($value) > $this->max_len) {
            $msg = "is longer than the maximum length of {$this->max_len}";
            throw new T_Exception_Filter($msg);
        }
        return (string) $value;
    }

    /**
     * Changes the maximum length.
     *
     * @param int $max_len  maximum allowed string length
     */
    function setMaxLength($max_len)
    {
        $this->max_len = (int) $max_len;
    }

}