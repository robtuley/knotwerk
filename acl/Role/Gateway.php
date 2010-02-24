<?php
/**
 * Defines the T_Role_Gateway class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Role gateway.
 *
 * @package ACL
 */
class T_Role_Gateway
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
     * Converts a row to a user role.
     *
     * @param array $row
     * @return T_Role
     */
    protected function toRole($row)
    {
        $row['id'] = (int) $row['id'];
        return $this->factory->like('T_Role',$row);
    }

    /**
     * Get role by id.
     *
     * @param int $id  ID number
     * @return T_Role
     */
    function getById($id)
    {
        $db = $this->db->slave();
        $sql = "SELECT id,name FROM role ".
               "WHERE id=?";
        $result = $db->query($sql,array($id));
        if (count($result)!=1) {
            throw new InvalidArgumentException("No ID $id");
        }
        return $this->toRole($result->fetch());
    }

    /**
     * Get role by name.
     *
     * @param string $name  name
     * @return T_Role
     */
    function getByName($name)
    {
        $db = $this->db->slave();
        $sql = "SELECT id,name FROM role ".
               "WHERE name=?";
        $result = $db->query($sql,array($name));
        if (count($result)!=1) {
            throw new InvalidArgumentException("No role name $name");
        }
        return $this->toRole($result->fetch());
    }

    /**
     * Get all roles.
     *
     * @return T_Role[]
     */
    function getAll()
    {
        $db = $this->db->slave();
        $result = $db->query('SELECT id,name FROM role ORDER BY name');
        $roles = array();
        foreach ($result as $row) {
            $r = $this->toRole($row);
            $roles[$r->getId()] = $r;
        }
        return $roles;
    }

    /**
     * Save user.
     *
     * @param T_Role $role
     * @return T_Role_Gateway  fluent interface
     */
    function save(T_Role $role)
    {
	return $role->getId() ? $this->update($role) : $this->insert($role);
    }

    /**
     * Insert role.
     *
     * @param T_Role $role
     * @return T_Role_Gateway  fluent interface
     */
    protected function insert(T_Role $role)
    {
        $db = $this->db->master();
        $db->begin();
        $sql = 'INSERT INTO role (name) '.
               'VALUES (?)';
        $db->query($sql,array($role->getName()));
        $role->setId($db->getLastId('role_id_seq'));
        $db->commit();
          // ^ transaction required to avoid any sequence
          //   race conditions with pgsql
        return $this;
    }

    /**
     * Add a role as a sub-member to another role.
     *
     * @param T_Role $group  group to add to
     * @param T_Role $role   member to add to
     * @return T_Role_Gateway  fluent interface
     */
    function addChild(T_Role $group,T_Role $member)
    {
        $db = $this->db->master();
        $data = array($group->getId(),$member->getId());
        $db->begin();
        $sql = 'DELETE FROM role_group '.
               'WHERE role=? AND member=?';
        $db->query($sql,$data);
        $sql = 'INSERT INTO role_group (role,member) '.
               'VALUES (?,?)';
        $db->query($sql,$data);
        $db->commit();
        return $this;
    }

    /**
     * Gets an array of all the roles that contain the given role.
     *
     * Note that since this method recursively queries the databse to estabilish all
     * parents, it can become expensive and should be used sparingly.
     *
     * @param string $role_name
     * @return T_Role[] $parents
     */
    function getParents($role_name)
    {
        $db = $this->db->slave();
        $roles = array();  // array keys are role IDs

        /* This process needs to recursively query the databse to find all parents of
         * the given role.
         *
         *     role A
         *       +---- role B
         *       +---- role C
         *               +---- role D
         *       +---- role E
         *
         * The method must pick up all of roles A,C and D if given the role D as an
         * input.
         */

        // get initial roles
        $sql = 'SELECT id,name '.
               'FROM role WHERE name=?';
        $result = $db->query($sql,array($role_name));

        // recurse for sub-members of user member roles
        while (count($result)>0) {
            foreach ($result as $row) $roles[intval($row['id'])] = $this->toRole($row);
            $existing = implode(',',array_keys($roles));
            $sql = 'SELECT id,name '.
                   'FROM role JOIN role_group ON (id=role) '.
                   "WHERE member IN ($existing) ".    // member is existing role
                   "AND role NOT IN ($existing)";     // parent not already registered
            $result = $db->query($sql);
        }

        return $roles;
    }

    /**
     * Delete a role.
     *
     * @param T_Role $role  role to delete
     * @return T_Role_Gateway  fluent interface
     */
    function delete(T_Role $role)
    {
        $sql = 'DELETE FROM role WHERE id=?';
        $this->db->master()->query($sql,array($role->getId()));
        $role->setId(null);
        return $this;
    }

    /**
     * Get roles from a particular user.
     *
     * @param T_User $user  user
     * @return T_Role[]
     */
    function getByUser(T_User $user)
    {
        $db = $this->db->slave();
        $id = (int) $user->getId();

        /* This process needs to recursively query the database until all roles
         * for a particular user have been retrieved. i.e. if the roles structure
         * looks like:
         *
         *     role A
         *       +---- role B
         *       +---- role C
         *               +---- role D
         *       +---- role E
         *
         * The method must pick up all of roles A,B,C,D,E for the user. As there
         * may be an arbitrary number of children, this cannot be achieved in one
         * query and several must be issued to retrieve the user roles.
         */
        $roles = array();  // array keys are role IDs

        // get initial roles
        $sql = 'SELECT id,name '.
               'FROM role JOIN person_role ON (id=role) '.
               "WHERE person=?";
        $result = $db->query($sql,array($id));

        // recurse for sub-members of user member roles
        while (count($result)>0) {
            foreach ($result as $row) $roles[intval($row['id'])] = $this->toRole($row);
            $existing = implode(',',array_keys($roles));
            $sql = 'SELECT id,name '.
                   'FROM role JOIN role_group ON (id=member) '.
                   "WHERE role IN ($existing) ".      // parent is existing role
                   "AND member NOT IN ($existing)";   // not already registered
            $result = $db->query($sql);
        }

        return $roles;
    }

    /**
     * Get role collection for a particular user.
     *
     * @param T_User $user  user
     * @return T_Role_Collection
     */
    function getCollectionByUser(T_User $user)
    {
        return new T_Role_Collection($this->getByUser($user));
    }

    /**
     * Allow a user a priviledge.
     *
     * @param T_User $user
     * @param string $role_name
     * @return T_Role_Gateway  fluent interface
     */
    function allow(T_User $user,$role_name)
    {
        $db = $this->db->master();
        $db->begin();
        $sql = 'SELECT id FROM role WHERE name=?';
        $id = $db->queryAndFetch($sql,array($role_name));
        $sql = 'DELETE FROM person_role '.
               'WHERE person=? AND role=?';
        $db->query($sql,array($user->getId(),$id));
        $sql = 'INSERT INTO person_role (person,role) '.
               'VALUES (?,?)';
        $db->query($sql,array($user->getId(),$id));
        $db->commit();
        return $this;
    }

    /**
     * Allow a user *all* current priviledges.
     *
     * @param T_User $user
     * @return T_Role_Gateway  fluent interface
     */
    function allowAll(T_User $user)
    {
        $db = $this->db->master();
        $db->begin();
        $sql = 'DELETE FROM person_role '.
               'WHERE person=?';
        $db->query($sql,array($user->getId()));
        $sql = 'INSERT INTO person_role (person,role) '.
               'SELECT '.(int) $user->getId().',id '.
               'FROM role';
        $db->query($sql);
        $db->commit();
        return $this;
    }

    /**
     * Deny a user a role.
     *
     * @param T_User $user
     * @param string $role_name
     * @return T_Role_Gateway  fluent interface
     */
    function deny(T_User $user,$role_name)
    {
        $sql = 'DELETE FROM person_role '.
               'WHERE person=? '.
               'AND role IN (SELECT id FROM role WHERE name=?)';
        $this->db->master()->query($sql,array($user->getId(),$role_name));
        return $this;
    }

    /**
     * Deny a user all roles.
     *
     * @param T_User $user
     * @return T_Role_Gateway  fluent interface
     */
    function denyAll(T_User $user)
    {
        $sql = 'DELETE FROM person_role '.
               'WHERE person=?';
        $this->db->master()->query($sql,array($user->getId()));
        return $this;
    }

}
