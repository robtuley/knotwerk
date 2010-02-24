<?php
/**
 * Contains the T_Controller_Context interface.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for all controller contexts.
 *
 * A controller context provides any given controller the scope that it resides
 * in. In other words it provides information such as what part of the URL has
 * already been mapped, what remains to be mapped, etc. Both the initial request
 * and all subsequent controllers maintain this context.
 *
 * @package controllers
 */
interface T_Controller_Context extends T_Environment_UrlContext
{

    /**
     * Gets the URL of the current context.
     *
     * @return T_Url  URL object
     */
    function getUrl();

    /**
     * Gets the sub space of the desired URL still to be mapped.
     *
     * @return array  path segments still to be mapped.
     */
    function getSubspace();

    /**
     * Gets the HTTP scheme this controller is coerced to (if any).
     *
     * @return string
     */
    function getCoerceScheme();

    /**
     * Whether this context was delegated.
     *
     * @return bool
     */
    function isDelegated();

}
