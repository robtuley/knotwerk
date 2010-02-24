<?php
/**
 * Contains the T_Template_Helper_Filter interface.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Template helper filter interface.
 *
 * If a template helper is a filter, the filter pre and post filters
 * are called round the output.
 *
 * @package views
 */
interface T_Template_Helper_Filter
{

    /**
     * Start of output buffering for view.
     */
    function start();

    /**
     * Output buffering is complete.
     */
    function complete();

}
