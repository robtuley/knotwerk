<?php
/**
 * Contains the T_Validate_UnixDate class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Convert to an integer UNIX local date.
 *
 * This filter converts a date to a unix time. If no format is specified,
 * the strtotime PHP function is used and therefore
 * only accepts dates in US format MM/DD/YY rather than UK format.
 * 
 * Alternatively, a format such as d|m|y can be specified: dates like 17/02/2002
 * will be converted to a UNIX time. The date parsing tries to be as flexible
 * as possible and will allow for example dates like 17-2-2002, 17.02.02 as 
 * much as possible.
 *
 * @package core
 */
class T_Validate_UnixDate extends T_Filter_Skeleton
{
    
    /**
     * Create filter.
     *
     * @param string $format  expected date format
     * @param function $filter  prior filter in chain
     */
    function __construct($format=null,$filter=null)
    {
        if (!is_null($format)) {
            $filter = new T_Validate_Date($format,$filter);
        }
        parent::__construct($filter);
    }

    /**
     * Converts to an integer UNIX local date.
     *
     * @param string $value  data to filter
     * @return int  UNIX date
     * @throws T_Exception_Filter  if the input is not a valid date
     */
    protected function doTransform($value)
    {
        if ($value instanceof T_Date) {
            // deal with situation where date is out of UNIX range (1970-2038)
            if ($value->getYear()<1970 || $value->getYear()>2038) {
                $unix_yrs = 'must be between the years 1970 and 2038';
                throw new T_Exception_Filter($unix_yrs);
            }
            $date = mktime(12,0,0,$value->getMonth(),$value->getDay(),$value->getYear());
        } else {
            $date = strtotime($value);
            if ($date === false) {  // compatible with PHP 5.1.0+ only
                throw new T_Exception_Filter("Invalid date $value");
            }
        }
        return $date;
    }
    
    /**
     * Get a UNIX time from a date using strtotime.
     *
     * @param string $value  date string
     * @return int  UNIX date
     */
    protected function guessFormat($value)
    {
        $date = strtotime($value,0);
        if ($date === false) {  // compatible with PHP 5.1.0+ only
            throw new T_Exception_Filter("Invalid date $value");
        }
        return $date;
    }
    
    /**
     * Get a UNIX time from a date using specified format.
     *
     * @param string $value  date string
     * @return int  UNIX date
     */
    protected function useFormat($value)
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
            if ($y>=70) {
                $y += 1900;
            } else {
                $y += 2000;
            }
        }
        // deal with situation where date is out of UNIX range (1970-2038)
        if ($y<1970 || $y>2038) {
            $unix_yrs = 'must be between the years 1970 and 2038';
            throw new T_Exception_Filter($unix_yrs);
        }
        if (!checkdate($m,$d,$y)) {
            throw new T_Exception_Filter($msg);
        }
        return mktime(12,0,0,$m,$d,$y);
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