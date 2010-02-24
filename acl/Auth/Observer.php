<?php
/**
 * Defines the T_Auth_Observer interface.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Auth observer.
 *
 * These are defined to 'watch' the authentication process and detect things like
 * hammer attacks, etc. They can be attached to methods that authenticate users and
 * when the events are triggered they may throw exceptions if there is a problem.
 *
 * @package ACL
 */
interface T_Auth_Observer
{

    /**
     * Authenticated OK.
     *
     * @param T_Auth  authentication
     * @return T_Auth_Observer  fluent interface
     */
    function pass(T_Auth $auth);

    /**
     * Authentication failure.
     *
     * @param T_Auth  authentication
     * @return T_Auth_Observer  fluent interface
     */
    function fail(T_Auth $auth);

}