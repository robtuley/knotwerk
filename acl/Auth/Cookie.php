<?php
/**
 * Defines the T_Auth_Cookie class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This driver stores and retrieves auth detail long-term (e.g. "remember-me") with a cookie token.
 *
 * @package ACL
 */
class T_Auth_Cookie implements T_Auth_Driver
{

    /**
     * Database connection.
     *
     * @var T_Db
     */
    protected $db;

    /**
     * Cookie controller.
     *
     * @var T_Cage_Cookie
     */
    protected $cookie;

    /**
     * Cookie name.
     *
     * @var string
     */
    protected $key;

    /**
     * Whether tokens are https only.
     *
     * @var bool
     */
    protected $https_only = false;

    /**
     * Create cookie auth driver.
     *
     * @param T_Db $db
     * @param T_Environment $env
     * @param string $key  cookie name
     */
    function __construct(T_Db $db,T_Environment $env,$key='auth')
    {
        $this->key = $key;
        $this->db = $db;
        $this->cookie = $env->input('COOKIE');
        if (!$this->cookie) {
            throw new InvalidArgumentException("No COOKIE available in environment");
        }
    }

    /**
     * Set auth cookie as HTTPS only.
     *
     * @return T_Auth_Cookie  fluent
     */
    function setHttpsOnly()
    {
        $this->https_only = true;
        return $this;
    }

    /**
     * Create token.
     *
     * @param T_User $user
     * @param int $expiry
     */
    protected function createToken(T_User $user,$expiry)
    {
        $token = md5(uniqid(rand()));
        $db = $this->db->master();
        $sql = 'INSERT INTO person_auth_token (token,person,expiry) '.
               'VALUES (?,?,?)';
        $data = array($token,$user->getId(),$expiry);
        $db->query($sql,$data);
        $this->cookie
             ->set($this->key,$token,$expiry,null,null,$this->https_only);
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
        if (!$this->cookie->exists($this->key)) return false;

        // lookup token in db
        $token = $this->cookie->asScalar($this->key)
                              ->filter(new T_Validate_HexHash())
                              ->uncage();
        if (mt_rand(1,20)==10) { // 1/20 clear old records
            $sql = 'DELETE FROM person_auth_token WHERE expiry<'.time();
            $this->db->master()->query($sql);
        }
        $sql = 'SELECT person,expiry '.
               'FROM person_auth_token '.
               'WHERE expiry>? AND token=?';
        $result = $this->db->slave()->query($sql,array(time(),$token));

        // if the token has not been found, remove it
        // (it has probably expired).
        if (count($result) != 1) {
            $this->destroy();
            return false;
        }

        // token has been found, so get the user and roles associated
        // with the token and create auth package
        $row = $result->fetch();
        $user = $user_gw->getById($row['person']);
        $role = $role_gw->getCollectionByUser($user);
        $auth = new T_Auth(T_Auth::TOKEN,$user,$role);

        // delete the use once token
        $sql = 'DELETE FROM person_auth_token '.
               'WHERE token=?';
        $this->db->master()->query($sql,array($token));

        // add a new persistent login token
        $this->createToken($user,$row['expiry']);

        return $auth;
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
        if ($expiry>time() && ($user=$auth->getUser()) ) {
            $this->createToken($user,$expiry);
        }
        return $this;
    }

    /**
     * Destroy any saved auth data.
     *
     * @return T_Auth_Driver  fluent interface
     */
    function destroy()
    {
        $this->cookie
             ->set($this->key,md5('hash'),time()-42000,null,null,$this->https_only);
        return $this;
    }

}
