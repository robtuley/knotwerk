<?php
/**
 * Contains the T_Filter_LimitedLengthText class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This checks the length of data coming through it and limits the TOTAL length to a
 * maximum value.
 *
 * @package wiki
 * @todo  test with multi-byte strings
 */
class T_Filter_LimitedLengthText extends T_Filter_Skeleton
{

    /**
     * Maximum length.
     *
     * @var int
     */
    protected $max_len;

    /**
     * Current position.
     *
     * @var position
     */
    protected $position = 0;

    /**
     * Delimiter to break text on (PCRE expressions valid).
     *
     * @var string
     */
    protected $delimiter;

    /**
     * Suffix to append to truncated content.
     *
     * @var string
     */
    protected $suffix;

    /**
     * Setup maximum length.
     *
     * @param int $max_len  maximum length
     * @param string $delimiter  delimiter(s) to break on (PCRE compatible)
     * @param string $suffix  suffix to append to limited text
     * @param function $filter  prior filter object
     */
    function __construct($max_len,
                         $delimiter,
                         $suffix,
                         $filter=null)
    {
        $this->max_len = (int) $max_len;
        $this->delimiter = $delimiter;
        $this->suffix = $suffix;
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
        if ($this->hasReachedLimit()) return false;
        $len = mb_strlen($value);
        $this->position += $len;
        /* if reached max this time round, attempt to break text on delimiter.
           At first, it is attempted to find the last break point before the
           max value is reached. If there isn't one, the last break *after*
           the maximum value is reached is used. Note we are NOT using the
           mb_* functions because the offset that the PCRE engine returns is
           in bytes -- we have to do a bit of juggling to make this multibyte
           character compatible as the max length is specified in characters.
           We are also making sure we don't break in the middle of repeated
           delimiters, and don't break at the start before any content.. */
        if ($this->hasReachedLimit()) {
            $target_len = $this->max_len-($this->position-$len);
            $byte_offset = false;
            preg_match_all("/[{$this->delimiter}]+/u",$value,$matches,PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $m) {
                $offset = $m[1];
                if ($offset<1) continue; /* do not break before some content */
                $trimmed = substr($value,0,$offset);
                if (mb_strlen($trimmed)<=$target_len) {
                    $byte_offset = $offset;
                } elseif ($byte_offset===false) {
                    /* if not found a break *before* the target len, grab
                       the first one afterwards. */
                    $byte_offset = $offset;
                }
            }
            if ($byte_offset !== false) {
                $value = substr($value,0,$byte_offset);
            }
            $value .= $this->suffix;
        }
        return $value;
    }

    /**
     * Whether the filter has reached max length.
     *
     * @return bool
     */
    function hasReachedLimit()
    {
        return $this->position > $this->max_len;
    }

}