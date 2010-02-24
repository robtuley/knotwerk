<?php
/**
 * Defines the T_User_Gateway class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * User gateway.
 *
 * @package ACL
 */
class T_User_Gateway
{

    /**
     * Database connection.
     *
     * @var T_Db
     */
    protected $db;

    /**
     * Factory.
     *
     * @var T_Factory
     */
    protected $factory;

    /**
     * Create gateway.
     *
     * @param T_Db $db
     * @param T_Factory $factory
     */
    function __construct(T_Db $db,T_Factory $factory)
    {
        $this->db = $db;
        $this->factory = $factory;
    }

    /**
     * Create user.
     *
     * @param array $row
     * @return T_User
     */
    protected function toUser($row)
    {
	return $this->factory->like('T_User',$row);
    }

    /**
     * Get email field.
     *
     * @return string
     */
    protected function getEmailField()
    {
        return 'email';
    }

    /**
     * Get fields to retrieve.
     *
     * @param string $type   either 'select','update' or 'insert'
     * @return array
     */
    protected function getFieldsFor($type)
    {
	return array($this->getEmailField());
    }

    /**
     * Get the tablename.
     *
     * @return string
     */
    protected function getTable()
    {
	return 'person';
    }

    /**
     * Gets the select SQL.
     *
     * @return string
     */
    protected function getSelectSql()
    {
	return 'SELECT '.$this->getFieldList($this->getFieldsFor('select')).
	       ' FROM '.$this->getTable().' ';
    }

    /**
     * Get a class method name from a fieldname.
     *
     * e.g. email -> getEmail
     *      line_one -> getLineOne
     *
     * @param string $field
     * @return method name
     */
    protected function getMethodFromField($field)
    {
        return 'get'.implode('',array_map('ucfirst',explode('_',$field)));
    }

    /**
     * Gets the array of fields and values.
     *
     * @param mixed $container  actual object to extract from
     * @param array $fields    array of fieldnames to extract
     * @param string $prefix  fieldname prefix
     * @return array
     */
    protected function getFieldValues($container,$fields,$prefix='')
    {
	$data = array();
        foreach ($fields as $key => $value) {
            if (is_array($value)) {  // is array, recurse
                $method = $this->getMethodFromField($key);
                $data += $this->getFieldValues($container->$method(),$value,$prefix.$key.'_');
            } else { // is actually a fieldname
                if ($container) {
                    // extract data from container
                    $method = $this->getMethodFromField($value);
                    $val = $container->$method();
        	    $data[$prefix.$value] = is_object($val) ? $val->getId() : $val;
                } else {
                    // container is null, so this field is no present
                    $data[$prefix.$value] = null;
                }
            }
        }
        return $data;
    }

    /**
     * Create a list of fields.
     *
     * @param  array of field names (possible nested)
     * @param string $prefix  fieldname prefix
     * @return string list of fields
     */
    protected function getFieldList($fields,$prefix='')
    {
        $list = '';
        if (!$prefix) array_unshift($fields,'id'); // if top level, add 'id' field
        foreach ($fields as $key => $name) {
            if (is_array($name)) { // is array, recurse
                $list .= $this->getFieldList($name,$prefix.$key.'_').',';
            } else {
                $list .= $this->getTable().".{$prefix}{$name},";
            }
        }
        return rtrim($list,',');
    }

    /**
     * Gets a user from a field value.
     *
     * @param string $field  fieldname
     * @param mixed $value  value
     * @return T_User
     */
    protected function getBy($field,$value)
    {
        $sql  = $this->getSelectSql().
                "WHERE $field = ?";
        $result = $this->db->slave()->query($sql,array($value));
        if (count($result) === 1) {
            return $this->toUser($result->fetch());
        } else {
            $msg = "User with $field=$value not found";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether a user exists by a particular field value.
     *
     * @param string $field  fieldname
     * @param mixed $value  value
     * @return T_User
     */
    protected function existsBy($field,$value)
    {
        $sql  = 'SELECT COUNT(*) FROM '.$this->getTable().' '.
                "WHERE $field = ?";
        return (bool) $this->db->slave()
	                       ->queryAndFetch($sql,array($value));
    }

    /**
     * Get user by id.
     *
     * @param int $id  ID number
     * @return T_User
     */
    function getById($id)
    {
        return $this->getBy($this->getTable().'.id',$id);
    }

    /**
     * Gets a single user by email.
     *
     * @param string $email  email
     * @return T_User
     */
    function getByEmail($email)
    {
	$field = $this->getTable().'.'.$this->getEmailField();
	if ($this->db->is(T_Db::POSTGRES)) {
	    $email = mb_strtolower($email);
	    $field = 'LOWER('.$field.')'; // postgres is case sensitive!
	}
        return $this->getBy($field,$email);
    }

    /**
     * Whether a user exists with an email.
     *
     * @param string $email  email
     * @return bool  whether a record exists
     */
    function existsByEmail($email)
    {
	$field = $this->getEmailField();
	if ($this->db->is(T_Db::POSTGRES)) {
	    $email = mb_strtolower($email);
	    $field = 'LOWER('.$field.')'; // postgres is case sensitive!
	}
        return $this->existsBy($field,$email);
    }

    /**
     * Get all users.
     *
     * @param string $order_by  optional orderby clause (e.g. 'name', 'name DESC', etc.)
     * @return T_User[]
     */
    function getAll($order_by=null)
    {
        $sql = $this->getSelectSql();
        if ($order_by) {
            $sql .= ' ORDER BY '.$order_by;
        } else {
            $sql .= ' ORDER BY '.$this->getTable().'.id';
        }
        $result = $this->db->slave()->query($sql);
        $users = array();
        foreach ($result as $row) {
            $users[$row['id']] = $this->toUser($row);
        }
        return $users;
    }

    /**
     * Save user.
     *
     * @param T_User $user
     * @return T_User_Gateway  fluent interface
     */
    function save(T_User $user)
    {
	return $user->getId() ? $this->update($user) : $this->insert($user);
    }

    /**
     * Insert user.
     *
     * @param T_User $user
     * @return T_User_Gateway  fluent interface
     */
    protected function insert(T_User $user)
    {
	$db = $this->db->master();
        $values = $this->getFieldValues($user,$this->getFieldsFor('insert'));
        $sql = 'INSERT INTO '.$this->getTable().
               ' ('.implode(',',array_keys($values)).') '.
               'VALUES ('.rtrim(str_repeat('?,',count($values)),',').')';
	$db->begin();
        $db->query($sql,array_values($values));
        $user->setId($db->getLastId($this->getTable().'_id_seq'));
	$db->commit();
	   // ^ transaction to prevent seq race conditions in pgsql
        return $this;
    }

    /**
     * Update user.
     *
     * @param T_User $user
     * @return T_User_Gateway  fluent interface
     */
    protected function update(T_User $user)
    {
        $values = $this->getFieldValues($user,$this->getFieldsFor('update'));
        $list = '';
        foreach ($values as $name=>$val) {
            $list .= "$name=?,";
        }
        $list = rtrim($list,',');
        $sql = 'UPDATE '.$this->getTable().
               ' SET '.$list.
               ' WHERE '.$this->getTable().'.id='.(int) $user->getId();
        $this->db->master()->query($sql,array_values($values));
        return $this;
    }

    /**
     * Delete user.
     *
     * @param T_User $user
     * @return T_User_Gateway  fluent interface
     */
    function delete(T_User $user)
    {
        $sql = "DELETE FROM ".$this->getTable()." WHERE id=".(int) $user->getId();
        $this->db->master()->query($sql);
        $user->setId(null);
        return $this;
    }

}
