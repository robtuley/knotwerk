<?php
/**
 * Defines the T_Filter_NormaliseEol class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Normalise Eol to application set value.
 *
 * @package forms
 */
class T_Filter_NormaliseEol extends T_Filter_Skeleton
{

    /**
     * EOL character to use.
     *
     * @var string
     */
    protected $eol;

    /**
     * Specify EOL character to use.
     *
     * @param string $eol
     */
    function __construct($eol=EOL,$filter=null)
    {
        parent::__construct($filter);
        $this->eol = $eol;
    }

    /**
     * Normalise EOL.
     *
     * @param string $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        $regex = '/(?:\r\n|\n|\x0b|\f|\x85)/';
        /* Matches newline characters: LF, CR, CRLF and unicode linebreaks.
           We can't use the more efficient '\R' here as it is only supported
           by PCRE 7.0+  */
        return preg_replace($regex,$this->eol,$value);
    }

}
