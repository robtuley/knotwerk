<?php
/**
 * Defines the T_User_Authenticator class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * User gateway that includes an authentication method and auth observers.
 *
 * @package ACL
 */
abstract class T_User_Authenticator extends T_User_Gateway
{

    /**
     * Auth observers.
     *
     * @var T_Auth_Observer[]
     */
    protected $observers = array();

    /**
     * Attach an auth observer.
     *
     * @param T_Auth_Observer $observer
     * @return OKT_UserPwdFactory  fluent interface
     */
    function attach(T_Auth_Observer $observer)
    {
        $this->observers[] = $observer;
        return $this;
    }

    /**
     * Get user by email and password.
     *
     * @return T_Auth  user auth
     * @throws  T_Exception_Auth if auth fails
     */

    // abstract function authenticate();
    //
    // Note that since authenticate in child classes will have a variable number
    // of arguments, etc. this is not explictally defined as an abstract method,
    // but nevertheless is assummed to exist by, for example, the T_Auth_Registry
    // class.

}
