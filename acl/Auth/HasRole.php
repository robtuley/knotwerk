<?php
/**
 * Defines the T_Auth_HasRole class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Specifies that there is a particular role in the auth.
 *
 * @package ACL
 */
class T_Auth_HasRole implements T_Auth_Spec
{

    /**
     * Role names.
     *
     * @var array
     */
    protected $roles;

    /**
     * Create role spec.
     *
     * @param string|array $role_name
     *
     */
    function __construct($role_name)
    {
        if (!is_array($role_name)) $role_name = array($role_name);
        $this->roles = $role_name;
    }

    /**
     * Whether roles are found.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth)
    {
        $role = $auth->getRole();
        foreach ($this->roles as $name) {
            if (!$role->is($name)) return false;
        }
        return true;
    }

}