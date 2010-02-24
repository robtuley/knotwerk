<?php
/**
 * Defines the T_Text_Header class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A header.
 *
 * @package wiki
 * @license http://knotwerk.com/licence MIT
 */
class T_Text_Header extends T_Text_Plain
{

    /**
     * The header level (1-6).
     *
     * @var int
     */
    protected $level;

    /**
     * Create header.
     *
     * @param int $level  header level (1-6)
     */
    function __construct($level,$content)
    {
        parent::__construct($content);
        $this->level = (int) $level;
        if ($this->level<1 || $this->level>6) {
            throw new InvalidArgumentException("illegal level $level");
        }
    }

    /**
     * Returns original formatted text.
     *
     * @return string  original formatting
     */
    function __toString()
    {
        $delimiter = str_repeat('=',$this->level+1);
        return $delimiter.' '.trim(parent::__toString()).' '.$delimiter.EOL.EOL;
    }

    /**
     * Get the header level.
     *
     * @return int  level
     */
    function getLevel()
    {
        return $this->level;
    }

}