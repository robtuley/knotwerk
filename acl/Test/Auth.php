<?php
/**
 * Unit test cases for the T_Auth class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Auth unit test cases.
 *
 * @package aclTests
 */
class T_Test_Auth extends T_Unit_Case
{

    /**
     * Gets a test user.
     *
     * @return T_User  test user instance
     */
    protected function getUser()
    {
        return new T_User(3,'test@example.com');
    }

    function testLevelIsSetInConstructor()
    {
        $auth = new T_Auth(T_Auth::HUMAN);
        $this->assertSame(T_Auth::HUMAN,$auth->getLevel());
    }

    function testLevelCanBeFilteredOnRetrieval()
    {
        $auth = new T_Auth(T_Auth::HUMAN);
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($f->transform(T_Auth::HUMAN),$auth->getLevel($f));
    }

    function testLevelCanBeChanged()
    {
        $auth = new T_Auth(T_Auth::HUMAN);
        $auth->setLevel(T_Auth::OBFUSCATED);
        $this->assertSame(T_Auth::OBFUSCATED,$auth->getLevel());
    }

    function testSetLevelMethodHasAFluentInterface()
    {
        $auth = new T_Auth(T_Auth::HUMAN);
        $test = $auth->setLevel(T_Auth::OBFUSCATED);
        $this->assertSame($auth,$test);
    }

    function testUserIsNullByDefault()
    {
        $auth = new T_Auth(T_Auth::HUMAN);
        $this->assertTrue(is_null($auth->getUser()));
    }

    function testUserIsSetInConstructor()
    {
        $user = $this->getUser();
        $auth = new T_Auth(T_Auth::TOKEN,$user);
        $this->assertSame($user,$auth->getUser());
    }

    function testUserCanBeChanged()
    {
        $user1 = $this->getUser();
        $user2 = $this->getUser();
        $auth = new T_Auth(T_Auth::TOKEN,$user1);
        $auth->setUser($user2);
        $this->assertSame($user2,$auth->getUser());
    }

    function testUserCanBeChangedToNull()
    {
        $user1 = $this->getUser();
        $auth = new T_Auth(T_Auth::TOKEN,$user1);
        $auth->setUser(null);
        $this->assertTrue(is_null($auth->getUser()));
    }

    function testSetUserMethodHasAFluentInterface()
    {
        $user = $this->getUser();
        $auth = new T_Auth(T_Auth::TOKEN,$user);
        $test = $auth->setUser(null);
        $this->assertSame($auth,$test);
    }

    function testRoleIsEmptyRoleCollectionByDefault()
    {
        $auth = new T_Auth(T_Auth::HUMAN);
        $this->assertTrue($auth->getRole() instanceof T_Role_Queryable);
    }

    function testRoleIsSetInConstructor()
    {
        $role = new T_Role_Collection(array());
        $auth = new T_Auth(T_Auth::TOKEN,null,$role);
        $this->assertSame($role,$auth->getRole());
    }

    function testRoleCanBeChanged()
    {
        $role1 = new T_Role_Collection(array());
        $role2 = new T_Role_Collection(array());
        $auth = new T_Auth(T_Auth::TOKEN,null,$role1);
        $auth->setRole($role2);
        $this->assertSame($role2,$auth->getRole());
    }

    function testSetRoleMethodHasAFluentInterface()
    {
        $role = new T_Role_Collection(array());
        $auth = new T_Auth(T_Auth::TOKEN,null,$role);
        $test = $auth->setRole($role);
        $this->assertSame($auth,$test);
    }

}