<?php
/**
 * Defines the T_Role_Queryable interface.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This defines an object which can be queryed as a Role.
 *
 * @package ACL
 */
interface T_Role_Queryable
{

    /**
     * Whether a particular role is present.
     *
     * @param string $role_name
     * @return bool  whether user has the role
     */
    function is($role_name);

    /**
     * Whether a role with a match is present.
     *
     * @param T_Pattern $pattern
     * @return bool  whether any role matches a pattern
     */
    function matches(T_Pattern $pattern);

}