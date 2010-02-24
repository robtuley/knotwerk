<?php
/**
 * Defines the T_Auth_IsLevel class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Specifies that the authorisation is at a particular level.
 *
 * @package ACL
 */
class T_Auth_IsLevel implements T_Auth_Spec
{

    /**
     * Level.
     *
     * @var int
     */
    protected $level;

    /**
     * Create level spec.
     *
     * @param int $level
     */
    function __construct($level)
    {
        $this->level = $level;
    }

    /**
     * Whether level is matched.
     *
     * @param T_Auth $auth
     * @return bool
     */
    function isSatisfiedBy($auth)
    {
        return (bool) ($this->level & $auth->getLevel());
          /* bit-wise operator is used here so the constructor argument can be
             more than 1 level e.g. T_Auth::HUMAN|T_Auth::OBFUSCATED */
    }

}