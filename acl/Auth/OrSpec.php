<?php
/**
 * Defines the T_Auth_OrSpec class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Join two specifications in an OR logical expression.
 *
 * @package ACL
 * @license http://knotwerk.com/licence MIT
 */
class T_Auth_OrSpec implements T_Auth_Spec
{

    /**
     * Base spec.
     *
     * @var T_Auth_Spec
     */
    protected $spec;

    /**
     * OR spec.
     *
     * @var T_Auth_Spec
     */
    protected $or_spec;

    /**
     * Create OR clause.
     *
     * @param T_Auth_Spec $spec
     * @param T_Auth_Spec $or_spec
     */
    function __construct(T_Auth_Spec $spec,T_Auth_Spec $or_spec)
    {
        $this->spec = $spec;
        $this->or_spec = $or_spec;
    }

    /**
     * Whether specification is satisfied.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth)
    {
        return $this->spec->isSatisfiedBy($auth) ||
               $this->or_spec->isSatisfiedBy($auth);
    }

}