<?php
/**
 * Date.
 *
 * In this library, dates are kept whenever possible to be integer UNIX timestamps.
 * However, when a broader range of dates is required, this class encapsulates a
 * date (time is not included). For example, it might be used to represent a
 * person's DOB or similar. Another use is recurring dates such as the same day
 * every year or similar.
 *
 * @package core
 */
class T_Date implements T_Decorated
{

    protected $day,$month,$year;

    /**
     * Create date.
     *
     * @param int $day
     * @param int $month
     * @param int $year
     */
    function __construct($day,$month,$year)
    {
        $this->setDay($day)
             ->setMonth($month)
             ->setYear($year);
    }

    /**
     * Sets the day.
     *
     * @param day $day  day
     * @return T_Date  fluent interface
     */
    function setDay($day)
    {
        $this->day = strlen($day)>0 ? (int) ltrim($day,'0') : null;
        return $this;
    }

    /**
     * Gets the day.
     *
     * @param function $filter  optional filter
     * @return int  day
     */
    function getDay($filter=null)
    {
        return _transform($this->day,$filter);
    }

    /**
     * Sets the month.
     *
     * @param int $month  month
     * @return T_Date  fluent interface
     */
    function setMonth($month)
    {
        $this->month = strlen($month)>0 ? (int) ltrim($month,'0') : null;
        return $this;
    }

    /**
     * Gets the month.
     *
     * @param function $filter  optional filter
     * @return int  month
     */
    function getMonth($filter=null)
    {
        return _transform($this->month,$filter);
    }

    /**
     * Sets the year.
     *
     * @param int $year  year
     * @return T_Date  fluent interface
     */
    function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * Gets the year.
     *
     * @param function $filter  optional filter
     * @return int  year
     */
    function getYear($filter=null)
    {
        return _transform($this->year,$filter);
    }

    /**
     * Retrieves the date in a particular format.
     *
     * This function converts the date into a particular format. The
     * following placeholders can be used (a limited selection from
     * the PHP date() function).
     *
     * +--------+------------------------------------+
     * | Code   | Description                        |
     * +--------+------------------------------------+
     * |   d    | numeric day with leading zeros     |
     * |   j    | numeric day with no leading zeros  |
     * |   m    | numeric month with leading zeros   |
     * |   n    | numeric month no leading zeros     |
     * |   Y    | full four-digit year               |
     * |   y    | 2 digit representation of year     |
     * +--------+------------------------------------+
     *
     * @param string $format  format string
     * @param T_Filter  optional filter to apply to output
     * @return string
     */
    function asFormat($format,$filter=null)
    {
        $short_year = strlen($this->year)==4 ? substr($this->year,2) : $this->year;
        $replace = array( 'd'=>str_pad($this->day,2,'0',STR_PAD_LEFT),
                          'j'=>$this->day,
                          'm'=>str_pad($this->month,2,'0',STR_PAD_LEFT),
                          'n'=>$this->month,
                          'Y'=>$this->year,
                          'y'=>$short_year );
        $date = str_replace(array_keys($replace),$replace,$format);
        return _transform($date,$filter);
    }

    // decoratable

    function getClass() { return get_class($this); }
    function isA($class) { return $this instanceof $class; }

}