<?php
/**
 * Defines the T_Role class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A role.
 *
 * @package ACL
 */
class T_Role implements T_Role_Queryable
{

    /**
     * ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Name.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Create role.
     *
     * @param int $id  ID
     * @param string $name  name
     */
    function __construct($id,$name)
    {
        $this->setId($id)->setName($name);
    }

    /**
     * Sets the ID.
     *
     * @param int $id ID
     * @return T_Role  fluent interface
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
     * Sets the role name.
     *
     * @param string $name  name
     * @return T_Role  fluent interface
     */
    function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the name.
     *
     * @param function $filter  optional output filter
     * @return string  name
     */
    function getName($filter = null)
    {
        return _transform($this->name,$filter);
    }

    /**
     * Whether the role has this name.
     *
     * @param string $name
     * @return bool  whether the role has this name
     */
    function is($name)
    {
        return strcmp($name,$this->name)==0;
    }

    /**
     * Whether the role name matches a pattern.
     *
     * @param T_Pattern $pattern
     * @return bool  whether name matches a pattern
     */
    function matches(T_Pattern $pattern)
    {
        return $pattern->isMatch($this->getName());
    }

}