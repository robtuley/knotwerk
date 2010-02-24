<?php
/**
 * Defines the T_Auth_PwdFactory interface.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Password factory.
 *
 * @package ACL
 */
interface T_Auth_PwdFactory
{

    /**
     * Create password.
     *
     * @return string  password string
     */
    function create();

}