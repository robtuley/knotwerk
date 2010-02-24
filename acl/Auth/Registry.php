<?php
/**
 * Defines the T_Auth_Registry class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * ACL (access control list) class.
 *
 * @package ACL
 */
class T_Auth_Registry
{

    /**
     * User gateway.
     *
     * @var T_User_Authenticator
     */
    protected $user_gw;

    /**
     * Role gateway.
     *
     * @var T_Role_Gateway
     */
    protected $role_gw;

    /**
     * Auth storage drivers.
     *
     * @var T_Auth_Driver[]
     */
    protected $drivers = array();

    /**
     * Current Auth.
     *
     * @var T_Auth
     */
    protected $auth = null;  // set to false once know no auth available

    /**
     * Current auth spec.
     *
     * @var T_Auth_Spec
     */
    protected $spec = false;

    /**
     * Create ACL controller.
     *
     * @param T_User_Gateway $user_gw  user gateway
     * @param T_Role_Gateway $role_gw  role gateway
     */
    function __construct(T_User_Authenticator $user_gw,T_Role_Gateway $role_gw)
    {
        $this->user_gw = $user_gw;
        $this->role_gw = $role_gw;
    }

    /**
     * Add a authentication driver.
     *
     * @return T_Auth_Registry
     */
    function addDriver(T_Auth_Driver $driver)
    {
        $this->drivers[] = $driver;
        return $this;
    }

    /**
     * Get the current authorisation.
     *
     * @return T_Auth
     */
    function getAuth()
    {
        if (is_null($this->auth)) {
            $failed = array();
            foreach ($this->drivers as $d) {
                $this->auth = $d->get($this->user_gw,$this->role_gw);
                if ($this->auth) break;  // once have retrieved an auth package, stop. This
                                         // means that multiple fallback drivers can be
                                         // used (e.g. session --> cookie token)
                $failed[] = $d;
            }
            // at this point we want the higher priority failed drivers to know what the
            // current auth is, so we work our way through them to set the current auth
            // level. e.g. auth may have retrieved from cookie
            // fallback auth, but we want to store in session (a higher priority driver).
            if ($this->auth) {
                foreach ($failed as $d) {
                    $d->save($this->auth);
                }
            }
        }
        if ($this->auth) {
            return $this->auth;
        } else {
            return new T_Auth(T_Auth::NONE,null,new T_Role_Collection(array()));
              // return an empty auth object if there is no authorisation
        }
    }

    /**
     * Set the authorisation.
     *
     * @param T_Auth $auth
     * @param int $expiry  expiry unix time
     * @return T_Auth_Registry  fluent interface
     */
    function setAuth(T_Auth $auth,$expiry=null)
    {
        $this->auth = $auth;
        foreach ($this->drivers as $d) {
            $d->save($auth,$expiry);
        }
        return $this;
    }

    /**
     * Deletes any existing authorisation.
     *
     * @return T_Auth_Registry  fluent interface
     */
    function deleteAuth()
    {
        $this->auth = false;
        foreach ($this->drivers as $d) {
            $d->destroy();
        }
        return $this;
    }

    /**
     * Gets the current auth spec.
     *
     * @return T_Auth_Spec
     */
    function getSpec()
    {
        return $this->spec;
    }

    /**
     * Sets the current auth spec.
     *
     * @param T_Auth_Spec $spec
     * @return T_Auth_Registry  fluent interface
     */
    function setSpec($spec)
    {
        $this->spec = $spec;
        return $this;
    }

    /**
     * Whether the spec set is satisfied.
     *
     * @return bool  whether the spec is satisfied
     */
    function isSatisfied()
    {
        $auth = $this->getAuth();
        $spec = $this->getSpec();
        if (!$spec) return true;   // no spec, so must be satisfied
        if (!$auth) return false;  // is a spec but no auth, so is *not* satisfied
        return $spec->isSatisfiedBy($auth);
    }

    /**
     * Tries to authenticate a user against the user gateway.
     *
     * @param mixed  any args are passed through to the user gateway
     * @return T_Auth_Registry  fluent interface
     * @throws T_Exception_Auth  if the authentication fails
     */
    function authenticate()
    {
        $args = func_get_args();
        $auth = call_user_func_array(array($this->user_gw,'authenticate'),$args);
        if ($auth) {
            if ($user=$auth->getUser()) { // populate roles from user
                $roles = $this->role_gw->getCollectionByUser($user);
                $auth->setRole($roles);
            }
            $this->setAuth($auth);
        }
        return $this;
    }

    /**
     * Attach an auth observer.
     *
     * @param T_Auth_Observer $observer
     * @return T_Auth_Registry  fluent interface
     */
    function attach(T_Auth_Observer $observer)
    {
        $this->user_gw->attach($observer);
        return $this;
    }

}
