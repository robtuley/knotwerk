<?php
/**
 * Defines the class T_Text_Divider class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A wiki divider (used to separate various pieces of text).
 *
 * @package wiki
 */
class T_Text_Divider extends T_Text_CompositeLeaf
{

    /**
     * Returns the string representation of a formatted text divider.
     *
     * @return string
     */
    function __toString()
    {
        return EOL.'----'.EOL;
    }

}