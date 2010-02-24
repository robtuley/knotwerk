<?php
/**
 * Contains the T_Validate_Salt class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Checks string is a salt of a certain length.
 *
 * @package core
 */
class T_Validate_Salt extends T_Filter_Skeleton
{

    /**
     * Length.
     *
     * @var string
     */
    protected $len;

    /**
     * Create filter.
     *
     * @param string $len  len if restricted
     * @param function $filter  prior filter object
     */
    function __construct($len=null,$filter=null)
    {
        $this->len = $len;
        parent::__construct($filter);
    }

    /**
     * Checks a salt string.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        if (!is_null($this->len) && strlen($value)!=$this->len) {
            throw new T_Exception_Filter("$value is not a len {$this->len} salt");
        }
        if (!ctype_alnum($value)) {
            throw new T_Exception_Filter("$value is not an alnum salt");
        }
        return $value;
    }

}