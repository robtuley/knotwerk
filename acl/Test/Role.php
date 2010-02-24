<?php
/**
 * Unit test cases for the T_Role class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Role unit test cases.
 *
 * @package aclTests
 */
class T_Test_Role extends T_Unit_Case
{

    /**
     * Gets a test role.
     *
     * @return T_Role  test role
     */
    protected function getRole()
    {
        return new T_Role(3,'a role name');
    }

    function testIdIsSetInConstructor()
    {
        $role = new T_Role(3,'a role name');
        $this->assertSame(3,$role->getId());
    }

    function testNameIsSetInConstructor()
    {
        $role = new T_Role(3,'a role name');
        $this->assertSame('a role name',$role->getName());
    }

    function testIdSetInConstructorCanBeNull()
    {
        $role = new T_Role(null,'a role name');
        $this->assertSame(null,$role->getId());
    }

    function testSetIdMethodHasFluentInterface()
    {
        $role = $this->getRole();
        $test = $role->setId(7);
        $this->assertSame($test,$role);
    }

    function testIdCanBeChanged()
    {
        $role = $this->getRole()->setId(8);
        $this->assertSame(8,$role->getId());
    }

    function testIdCanBeChangedToNull()
    {
        $role = $this->getRole()->setId(null);
        $this->assertSame(null,$role->getId());
    }

    function testSetNameMethodHasAFluentInterface()
    {
        $role = $this->getRole();
        $test = $role->setName('alternative role name');
        $this->assertSame($test,$role);
    }

    function testNameCanBeChanged()
    {
        $role = $this->getRole()->setName('alternative role name');
        $this->assertSame('alternative role name',$role->getName());
    }

    function testNameCanBeFilteredOnRetrieval()
    {
        $role = $this->getRole();
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($f->transform($role->getName()),$role->getName($f));
    }

    function testIsReturnsFalseWhenDifferentName()
    {
        $role = new T_Role(3,'role_name');
        $this->assertFalse($role->is('diff_name'));
    }

    function testIsReturnsTrueWhenNameMatches()
    {
        $role = new T_Role(3,'role_name');
        $this->assertTrue($role->is('role_name'));
    }

    function testIsMethodHasCaseSensitiveComparison()
    {
        $role = new T_Role(3,'role_name');
        $this->assertFalse($role->is('RolE_NamE'));
    }

    function testMatchesReturnsFalseWhenNoPatternMatchToName()
    {
        $role = new T_Role(3,'role_name');
        $this->assertFalse($role->matches(new T_Pattern_Regex('/nomatch/')));
    }

    function testMatchesReturnsTrueWhenIsPatternMatchToName()
    {
        $role = new T_Role(3,'role_name');
        $this->assertTrue($role->matches(new T_Pattern_Regex('/role/')));
    }

}