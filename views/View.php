<?php
/**
 * Contains the T_View interface.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * View interface.
 *
 * A view is basically any object that can be echoed. As such, all an object
 * needs to be a view is to possess the PHP magic __toString() method.
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
interface T_View
{

    /**
     * Outputs the view to the output buffer.
     *
     * @return T_View  fluent interface
     */
    function toBuffer();

    /**
     * Object must be able to be represented as a string.
     *
     * @return string  text representation of the object
     */
    function __toString();

}