<?php
/**
 * Defines the T_Role_Collection class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This encapsulates a collection of roles.
 *
 * @package ACL
 */
class T_Role_Collection implements T_Role_Queryable
{

    /**
     * An array of roles.
     *
     * @var T_Role[]
     */
    protected $roles;

    /**
     * Create a collection of roles.
     *
     * @param T_Role[] $roles
     */
    function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Whether a particular role is in collection.
     *
     * @param string $role_name
     * @return bool  whether user has the role
     */
    function is($role_name)
    {
        foreach ($this->roles as $r) {
            if ($r->is($role_name)) return true;
        }
        return false;
    }

    /**
     * Whether a role with a match is present.
     *
     * @param T_Pattern $pattern
     * @return bool  whether any role matches a pattern
     */
    function matches(T_Pattern $pattern)
    {
        foreach ($this->roles as $r) {
            if ($r->matches($pattern)) return true;
        }
        return false;
    }

}