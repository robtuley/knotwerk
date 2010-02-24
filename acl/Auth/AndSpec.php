<?php
/**
 * Defines the T_Auth_AndSpec class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Join two specifications in an AND logical expression.
 *
 * @package ACL
 * @license http://knotwerk.com/licence MIT
 */
class T_Auth_AndSpec implements T_Auth_Spec
{

    /**
     * Base spec.
     *
     * @var T_Auth_Spec
     */
    protected $spec;

    /**
     * AND spec.
     *
     * @var T_Auth_Spec
     */
    protected $and_spec;

    /**
     * Create AND clause.
     *
     * @param T_Auth_Spec $spec
     * @param T_Auth_Spec $and_spec
     */
    function __construct(T_Auth_Spec $spec,T_Auth_Spec $and_spec)
    {
        $this->spec = $spec;
        $this->and_spec = $and_spec;
    }

    /**
     * Whether specification is satisfied.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth)
    {
        return $this->spec->isSatisfiedBy($auth) &&
               $this->and_spec->isSatisfiedBy($auth);
    }

}