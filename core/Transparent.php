<?php
/**
 * Contains the T_Transparent interface.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a "transparent" decorator.
 *
 * @package core
 */
interface T_Transparent
{

    /**
     * Returns the object under the transparent decorator.
     *
     * @return object
     */
    function lookUnder();

}
