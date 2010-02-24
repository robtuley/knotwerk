<?php
/**
 * Defines the T_Auth_Driver interface.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This defines a way of saving and retrieving auth details.
 *
 * @package ACL
 */
interface T_Auth_Driver
{

    /**
     * Get any auth available.
     *
     * @param T_User_Gateway $user_gw
     * @param T_Role_Gateway $role_gw
     * @return T_Auth|false  auth if available or false if not
     */
    function get($user_gw,$role_gw);

    /**
     * Save an authorisation.
     *
     * @param T_Auth $auth
     * @param int $expiry  expiry unix time
     * @return T_Auth_Driver  fluent interface
     */
    function save(T_Auth $auth,$expiry=null);

    /**
     * Destroy any saved auth data.
     *
     * @return T_Auth_Driver  fluent interface
     */
    function destroy();

}