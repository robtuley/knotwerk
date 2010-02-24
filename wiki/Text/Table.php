<?php
/**
 * Defines the T_Text_Table class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A table container.
 *
 * @package wiki
 */
class T_Text_Table extends T_Text_Composite
{

    /**
     * Returns original formatted full text.
     */
    function __toString()
    {
        // @todo format table nicely
        return parent::__toString();
    }

}