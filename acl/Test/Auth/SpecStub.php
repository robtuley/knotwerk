<?php
/**
 * Defines the T_Test_Auth_SpecStub class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test Auth Spec.
 *
 * @package aclTests
 */
class T_Test_Auth_SpecStub implements T_Auth_Spec,T_Test_Stub
{

    /**
     * Whether spec is satisfied.
     *
     * @var bool
     */
    protected $is_satisfied;

    /**
     * Whether sepc is satisfied.
     *
     * @param bool $is_satisfied
     */
    function __construct($is_satisfied)
    {
        $this->is_satisfied = $is_satisfied;
    }

    /**
     * Whether specification is satisfied.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth)
    {
        return $this->is_satisfied;
    }

}