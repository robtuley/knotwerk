<?php
/**
 * Defines the T_Text_Quote class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A quote (rendered in XHTML as a blockquote).
 *
 * @package wiki
 */
class T_Text_Quote extends T_Text_Plain
{

    /**
     * Citation (i.e. source).
     *
     * @var T_Text_Plain
     */
    protected $cite;

    /**
     * Create quote.
     *
     * @param string $cite citation source
     * @param string $content  quotation text
     */
    function __construct($cite,$content)
    {
        parent::__construct($content);
        $cite = mb_trim($cite);
        $this->cite = strlen($cite) ? new T_Text_Citation($cite) : null;
    }

    /**
     * Returns original formatted text.
     *
     * @return string  original formatting
     */
    function __toString()
    {
        return '""'.EOL.trim(parent::__toString()).EOL.'"" '.$this->cite->__toString().EOL.EOL;
    }

	/**
	 * Gets the citation.
	 *
	 * @param function $filter  optional filter
	 * @return T_Text_Plain  citation source
	 */
	function getCite($filter=null)
	{
	    return _transform($this->cite,$filter);
	}

    /**
     * Accept a visitor.
     *
     * A quotation when it accepts a visitor needs to parse no only to visit the
     * main quotation element, but also the citation element if available
     * afterwards.
     *
     * @param T_Visitor $visitor  visitor object
     */
    function accept(T_Visitor $visitor)
    {
        parent::accept($visitor);
        if ($this->getCite()) {
            $this->getCite()->accept($visitor);
        }
    }

}