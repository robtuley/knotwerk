<?php
/**
 * Defines the T_Text_Paragraph class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A paragraph of text.
 *
 * @package wiki
 * @license http://knotwerk.com/licence MIT
 */
class T_Text_Paragraph extends T_Text_Plain
{

    /**
     * Returns original formatted text.
     *
     * @return string  origianl formatting
     */
    function __toString()
    {
        return trim(parent::__toString()).EOL.EOL;
    }

}