<?php
/**
 * Defines the T_Auth class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This encapsulates the authorisation user/level of the current request.
 *
 * @package ACL
 */
class T_Auth
{

    /**
     * Auth constants
     */
    const NONE=0;
    const HUMAN=1; // is human (e.g after captcha)
    const OBFUSCATED=2; // hard to guess url
    const TOKEN=4; // by a token e.g. cookie
    const CHALLENGED=8; // has been challenged this session
    const USER=12; // self::TOKEN|self::CHALLENGED;

    /**
     * User.
     *
     * @var T_User
     */
    protected $user;

    /**
     * Auth level (obfuscated, human, etc.)
     *
     * @var int
     */
    protected $level;

    /**
     * Role array.
     *
     * @var T_Role_Collection
     */
    protected $role;

    /**
     * Create request auth.
     *
     * @param int $level
     * @param T_User $user
     * @param T_Role_Queryable $role
     */
    function __construct($level,$user=null,T_Role_Queryable $role=null)
    {
        if (is_null($role)) {
            $role = new T_Role_Collection(array());
        }
        $this->setUser($user)
             ->setLevel($level)
             ->setRole($role);
    }

    /**
     * Set user.
     *
     * @param T_User $user  user
     * @return T_Auth  fluent interface
     */
    function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the user.
     *
     * @return T_User  user
     */
    function getUser()
    {
        return $this->user;
    }

    /**
     * Set level.
     *
     * @param int $level  user
     * @return T_Auth  fluent interface
     */
    function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * Get the level.
     *
     * @param function $filter  optional filter to apply
     * @return int  level
     */
    function getLevel($filter=null)
    {
        return _transform($this->level,$filter);
    }

    /**
     * Set role.
     *
     * @param T_Role_Queryable $role  role collection
     * @return T_Auth  fluent interface
     */
    function setRole(T_Role_Queryable $role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Get the role.
     *
     * @return T_Role_Queryable  role collection
     */
    function getRole()
    {
        return $this->role;
    }

}
