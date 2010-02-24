<?php
/**
 * Contains the T_Validate_Date class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Convert a string to a date.
 *
 * This filter converts a string to an T_Date object based on a particular format.
 * A format such as d|m|y can be specified which will result in dates like 17/02/2002
 * will be converted to a UNIX time. The date parsing tries to be as flexible
 * as possible and will allow for example dates like 17-2-2002, 17.02.02 as
 * much as possible.
 *
 * @package core
 */
class T_Validate_Date extends T_Filter_Skeleton
{

    /**
     * Format (e.g. array(d,m,y)).
     *
     * On input in constructor delimiters (slash, dot, dash) are represented by
     * a pipe. These are then exploded into an array which represents all the
     * different parts of the date. A month is represented by 'm',day by 'd',
     * year by 'y'.
     *
     * @var array
     */
    protected $fmt;

    /**
     * Create filter.
     *
     * @param string $format  expected date format
     * @param function $filter  prior filter in chain
     */
    function __construct($format,$filter=null)
    {
        parent::__construct($filter);
        /* Format (e.g. d|m|y for UK):
         	o Invalid if contains anything other than: |,m,d,y
            o each 'term' must be a single type e.g. d|my is invalid */
        if (strlen($format)<1) {
            throw new InvalidArgumentException('Zero length date format');
        }
        $format = explode('|',$format);
        $valid = array('d','m','y');
        foreach ($format as $term) {
            if (!in_array($term,$valid,true)) {
                throw new InvalidArgumentException("Invalid format $format");
            }
        }
        $this->fmt = $format;
    }

    /**
     * Converts to a date object.
     *
     * @param string $value  data to filter
     * @return int  UNIX date
     * @throws T_Exception_Filter  if the input is not a valid date
     */
    protected function doTransform($value)
    {
        // set today as defaults
        $today = getdate();
        $d = $today['mday']; $m = $today['mon']; $y = $today['year'];
        // build error message
        $msg = 'must be in the format '.$this->getHumanFormat();
        // parse input (delimiters /-.)
        $terms = preg_split('/[\\/\\.-]/',$value,-1,PREG_SPLIT_NO_EMPTY);
        if (count($terms)!==count($this->fmt)) {
            throw new T_Exception_Filter($msg);
        }
        for ($i=0, $max=count($this->fmt); $i<$max; $i++) {
            ${$this->fmt[$i]} = (int) ltrim($terms[$i],'0');
            // note variable variable on LHS, and strip zero padding
            // from front of user input on RHS.
        }
        // deal with two digit years
        if ($y<100) {
            if ($y>=39) {
                $y += 1900;
            } else {
                $y += 2000;
            }
        }
        if (!checkdate($m,$d,$y)) {
            throw new T_Exception_Filter($msg);
        }
        return new T_Date($d,$m,$y);
    }

    /**
     * Converts format to human readable.
     *
     * @return string
     */
    protected function getHumanFormat()
    {
        $human = array('d'=>'dd','m'=>'mm','y'=>'yyyy');
        foreach ($this->fmt as $term) {
            $fmt[] = $human[$term];
        }
        return implode('/',$fmt);
    }

}