<?php
/**
 * Defines the T_Validate_WordLimit class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that a string falls within a certain word limit.
 *
 * @package forms
 */
class T_Validate_WordLimit extends T_Filter_Skeleton
{

    /**
     * Min.
     *
     * @var int
     */
    protected $min;

    /**
     * Max.
     *
     * @var int
     */
    protected $max;

    /**
     * Create filter.
     *
     * @param int $min  minimum (null for unlimited)
     * @param int $max  maximum (null for unlimited)
     * @param function $filter  The prior filter object
     */
    function __construct($min,$max,$filter=null)
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($filter);
    }

    /**
     * Checks string falls into specified word count range.
     *
     * @param string $value  data to filter
     * @return string  filtered value
     * @throws T_Exception_Filter  if word count is out of range
     */
    protected function doTransform($value)
    {
        $regex = "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u";
        // This regex matches individual words where a 'word':
        //   o starts with a letter (\p{L})
        //   o may contain letters, non-spacing marks (\p{Mn}), dashes (\p{Pd}),
        //     apostrophe (', and \x{2019} is alternative apostrophe mark).
        $word_count = preg_match_all($regex,$value,$matches);
        if (!is_null($this->min) && $word_count<$this->min) {
            $msg = "has too few words: minimum word count is {$this->min} and you have written $word_count";
            throw new T_Exception_Filter($msg);
        }
        if (!is_null($this->max) && $word_count>$this->max) {
            $msg = "has too many words: maximum is {$this->max} and you have written $word_count";
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}
