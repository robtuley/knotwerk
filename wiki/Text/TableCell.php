<?php
/**
 * Defines the T_Text_TableCell class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A table cell.
 *
 * @package wiki
 */
class T_Text_TableCell extends T_Text_Plain
{

    /**
     * Cell types.
     */
    const PLAIN = 1;
    const HEADER = 2;

    /**
     * Cell type.
     *
     * @var string
     */
    protected $type;

    /**
     * Span level.
     *
     * @var int
     */
    protected $span;

    /**
     * Create table cell.
     *
     * @param string $content  content
     * @param int $type
     */
    function __construct($content,$type=self::PLAIN,$span=1)
    {
        $this->type = $type;
        $this->span = $span;
        parent::__construct($content);
    }

    /**
     * Whether the table cell is of a particular type.
     *
     * @param int $type
     * @return bool
     */
    function is($type)
    {
        return $type===$this->type;
    }

    /**
     * Gets the span level.
     *
     * @param function $filter
     * @return int
     */
    function getSpan($filter=null)
    {
        return _transform($this->span,$filter);
    }

}