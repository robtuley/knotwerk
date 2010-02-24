<?php
/**
 * Defines the T_Auth_Spec interface.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Auth specification.
 *
 * @package ACL
 * @license http://knotwerk.com/licence MIT
 */
interface T_Auth_Spec
{

    /**
     * Whether specification is satisfied.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth);

}