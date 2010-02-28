<?php
/**
 * Defines the T_Auth_Session class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This driver stores and retrieves auth detail from session.
 *
 * @package ACL
 */
class T_Auth_Session implements T_Auth_Driver
{

    /**
     * Session key.
     *
     * @var string
     */
    protected $key;

    /**
     * Session handler.
     *
     * @var T_Session_Handler
     */
    protected $session;

    /**
     * Create session auth driver.
     *
     * @param string $key  session key
     */
    function __construct(T_Session_Handler $session,$key='user/auth')
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * Get any auth available.
     *
     * @param T_User_Gateway $user_gw
     * @param T_Role_Gateway $role_gw
     * @return T_Auth|false  auth if available or false if not
     */
    function get($user_gw,$role_gw)
    {
        if ($this->session->exists($this->key)) {
            return $this->session->get($this->key);
        } else {
            return false;
        }
    }

    /**
     * Save an authorisation.
     *
     * @param T_Auth $auth
     * @param int $expiry  expiry unix time
     * @return T_Auth_Driver  fluent interface
     */
    function save(T_Auth $auth,$expiry=null)
    {
        $this->session->regenerate();
           // regenerate session on change in privilege level is essential to
           // mitigate the risk of session fixation attacks
        $this->session->set($this->key,$auth);
        return $this;
    }

    /**
     * Destroy any saved auth data.
     *
     * @return T_Auth_Driver  fluent interface
     */
    function destroy()
    {
        $this->session->delete($this->key);
        return $this;
    }

}
