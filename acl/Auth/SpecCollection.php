<?php
/**
 * Defines the T_Auth_SpecCollection class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This can be used to encapsulate a number of auth spec conditions.
 *
 * @package ACL
 */
class T_Auth_SpecCollection implements T_Auth_Spec
{

    /**
     * Spec.
     *
     * @var T_Auth_Spec
     */
    protected $spec;

    /**
     * Create base spec.
     *
     * @param T_Auth_Spec $spec  optional base spec
     */
    function __construct(T_Auth_Spec $spec)
    {
        $this->spec = $spec;
    }

    /**
     * Add an additional spec that must be met as well as the previous spec.
     *
     * @param T_Auth_Spec $spec
     */
    function andSpec(T_Auth_Spec $spec)
    {
        $this->spec = new T_Auth_AndSpec($this->spec,$spec);
        return $this;
    }

    /**
     * Add a spec that can be met as an alternative to the existing spec.
     *
     * @param T_Auth_Spec $spec
     */
    function orSpec(T_Auth_Spec $spec)
    {
        $this->spec = new T_Auth_OrSpec($this->spec,$spec);
        return $this;
    }

    /**
     * Whether specification is satisfied.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth)
    {
        return $this->spec->isSatisfiedBy($auth);
    }

}