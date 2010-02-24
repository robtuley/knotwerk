<?php
/**
 * Defines the T_User class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * User.
 *
 * @package ACL
 * @license http://knotwerk.com/licence MIT
 */
class T_User
{

    /**
     * ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Email address.
     *
     * @var string
     */
    protected $email = null;

    /**
     * Create user.
     *
     * @param int $id  ID
     * @param string $email  email
     */
    function __construct($id,$email)
    {
        $this->setId($id)->setEmail($email);
    }

    /**
     * Sets the ID.
     *
     * @param int $id ID
     * @return T_User  fluent interface
     */
    function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Gets the ID.
     *
     * @return int  ID
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Sets the email address.
     *
     * @param string $email  email
     * @return T_User  fluent interface
     */
    function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Gets the email.
     *
     * @param function $filter  optional output filter
     * @return string  email
     */
    function getEmail($filter = null)
    {
        return _transform($this->email,$filter);
    }

}