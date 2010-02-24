<?php
/**
 * Defines the T_User_PwdGateway class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * User gateway that includes password authentication methods.
 *
 * Note that this gateway will store the password as a binary string. This
 * should be stored as a binary byte string of 40 characters.
 * <code>
 * CREATE TABLE user ( ..., pwd BINARY(40), salt VARCHAR(40), ... );
 * </code>
 *
 * @package ACL
 */
class T_User_PwdGateway extends T_User_Authenticator
{

    /**
     * Get a password field.
     *
     * @return string
     */
    protected function getPwdField()
    {
        return 'pwd';
    }

    /**
     * Get password salt field.
     *
     * @return string
     */
    protected function getSaltField()
    {
        return 'salt';
    }

    /**
     * Get fields to retrieve.
     *
     * @param string $type   either 'select','update' or 'insert'
     * @return array
     */
    protected function getFieldsFor($type)
    {
        $fields = parent::getFieldsFor($type);
        if (strcmp($type,'update')!==0) $fields[] = $this->getSaltField();
        return $fields;
    }

    /**
     * Hashes the password with the user salt.
     *
     * @param string $pwd   password
     * @param T_user $user  user
     * @return string
     */
    protected function hashPwd($pwd,T_User $user)
    {
        $method = $this->getMethodFromField($this->getSaltField());
        return sha1($pwd.$user->$method());
    }

    /**
     * Set a user's password.
     *
     * @param string $pwd
     * @param T_User $user
     * @return T_User_PwdGateway  fluent interface
     */
    function setPwd($pwd,T_User $user)
    {
        $db = $this->db->master();
        $sql = 'UPDATE '.$this->getTable().' SET '.
               $this->getPwdField().'=? WHERE id=?';
        $this->db->master()->query($sql,
                array($this->hashPwd($pwd,$user),$user->getId()) );
        return $this;
    }

    /**
     * Whether a string is a user's password.
     *
     * @param string $pwd
     * @param T_User $user
     * @return bool  whether password is user's password
     */
    function isPwd($pwd,T_User $user)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->getTable().' '.
               'WHERE id=? AND '.$this->getPwdField().'=?';
        $data = array($user->getId(),$this->hashPwd($pwd,$user));
        return (bool) $this->db->slave()->queryAndFetch($sql,$data);
    }

    /**
     * Get user by email and password.
     *
     * @param string $field  field name (e.g. 'email')
     * @param string $value  field value
     * @param string $pwd  password
     * @return T_Auth  user auth
     * @throws  T_Exception_Auth if no user found
     */
    function authenticate($email,$pwd)
    {
        $fail = false;
        try {
            $user = $this->getByEmail($email);
            if (!$this->isPwd($pwd,$user)) {
                $fail = new T_Auth(T_Auth::CHALLENGED,$user);
            }
        } catch (InvalidArgumentException $e) {
            $fail = new T_Auth(T_Auth::CHALLENGED);
        }
        if (false!==$fail) {
            foreach ($this->observers as $o) {
                $o->fail($fail);
            }
            $msg = 'The details provided do not match our records. Please try again.';
            throw new T_Exception_Auth($msg);
        }
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        foreach ($this->observers as $o) {
            $o->pass($auth);
        }
        return $auth;
    }

}
