<?php
/**
 * Unit test cases for the T_Role_Collection class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Role_Collection unit test cases.
 *
 * @package aclTests
 */
class T_Test_Role_Collection extends T_Unit_Case
{

    function testEmptyRoleCollectionHasNoRoles()
    {
        $group = new T_Role_Collection(array());
        $this->assertFalse($group->is('role_name'));
    }

    function testEmptyRoleCollectionHasNoRolesMatches()
    {
        $group = new T_Role_Collection(array());
        $this->assertFalse($group->matches(new T_Pattern_Regex('/role/')));
    }

    function testIsReturnsTrueForSingleRoleInCollection()
    {
        $group = new T_Role_Collection(array(new T_Role(3,'role_name')));
        $this->assertTrue($group->is('role_name'));
        $this->assertFalse($group->is('role'));
        $this->assertFalse($group->is('name'));
        $this->assertFalse($group->is('rOLe_NAme'));
    }

    function testIsReturnsTrueForMultipleRoleInCollection()
    {
        $roles = array( new T_Role(3,'role_name'),
                        new T_Role(5,'diff_name'));
        $group = new T_Role_Collection($roles);
        $this->assertTrue($group->is('role_name'));
        $this->assertTrue($group->is('diff_name'));
        $this->assertFalse($group->is('role'));
        $this->assertFalse($group->is('name'));
    }

    function testMatchesReturnsTrueForSingleRoleInCollection()
    {
        $group = new T_Role_Collection(array(new T_Role(3,'role_name')));
        $this->assertTrue($group->matches(new T_Pattern_Regex('/role/')));
        $this->assertFalse($group->matches(new T_Pattern_Regex('/notamatch/')));
    }

    function testMatchesReturnsTrueForMultipleRoleInCollection()
    {
        $roles = array( new T_Role(3,'role_name'),
                        new T_Role(5,'diff_name'));
        $group = new T_Role_Collection($roles);
        $this->assertTrue($group->matches(new T_Pattern_Regex('/role/')));
        $this->assertTrue($group->matches(new T_Pattern_Regex('/name/'))); // matches both
        $this->assertFalse($group->matches(new T_Pattern_Regex('/notamatch/')));
    }

}