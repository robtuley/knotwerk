<?php
class T_Test_Role_Gateway extends T_Unit_Case
{

    protected $dbs;

    function setUpSuite()
    {
        $factory = $this->getFactory();
        $this->dbs = $factory->getAllDb();
        $factory->setupSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);
        $this->cycleOn('db',$this->dbs);
    }

    function getRoleGateway($db)
    {
        return new T_Role_Gateway($db,
                                  new T_Factory_Di());
    }

    function getUserGateway($db)
    {
        return new T_User_Gateway($db,
                                  new T_Factory_Di());
    }

    function tearDownSuite()
    {
        $this->getFactory()
             ->teardownSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);
    }

    function createRole()
    {
        return new T_Role(null,
                          uniqid('role',true));
    }

    function insertRole(T_Role_Gateway $gateway)
    {
        $gateway->save($role=$this->createRole());
        return $role;
    }

    function createUser()
    {
        return new T_User(null,
                          uniqid('user',true).'@example.com');
    }

    function insertUser(T_User_Gateway $gateway)
    {
        $gateway->save($user=$this->createUser());
        return $user;
    }

    function deleteAll($db)
    {
        $db->master()->query('DELETE FROM person');
        $db->master()->query('DELETE FROM role');
    }

    function testThatInsertCreatesId($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->createRole();
        $this->assertSame($gateway,$gateway->save($role));
        $this->assertTrue(is_integer($role->getId()));
    }

    function testRoleCanBeRetrievedById($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $test = $gateway->getById($role1->getId());
        $this->assertEquals($role1,$test);
    }

    function testGetByIdFailsWhenNoRoleFound($db)
    {
        $gateway = $this->getRoleGateway($db);
        try {
            $gateway->getById(-1);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testDeleteRemovesRoleFromDatabase($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $id = $role->getId();
        $gateway->delete($role);
        try {
            $gateway->getById($id);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testDeleteDoesNotAffectOtherRoles($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $gateway->delete($role1);
        $this->assertEquals($role2,$gateway->getById($role2->getId()));
    }

    function testDeleteSetsIdToNull($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $gateway->delete($role);
        $this->assertTrue(is_null($role->getId()));
    }

    function testDeleteHasAFluentInterface($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $test = $gateway->delete($role);
        $this->assertSame($gateway,$test);
    }

    function testGetAllWithEmptyRoleTable($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $roles = $gateway->getAll();
        $this->assertSame(array(),$roles);
    }

    function testGetAllWithSingleRole($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $roles = $gateway->getAll();
        $this->assertEquals(array($role->getId()=>$role),$roles);
    }

    function testGetAllWithMultipleRoles($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->createRole()->setName('def');
        $role2 = $this->createRole()->setName('abc');
        $gateway->save($role1)
                ->save($role2);
        $expect = array( $role2->getId()=>$role2,  // ordered by name
                         $role1->getId()=>$role1 );
        $this->assertEquals($expect,$gateway->getAll());
    }

    function testAddChildHasAFluentInterface($db)
    {
        $gateway = $this->getRoleGateway($db);
        $group = $this->insertRole($gateway);
        $member = $this->insertRole($gateway);
        $test = $gateway->addChild($group,$member);
        $this->assertSame($test,$gateway);
    }

    function testAddChildCanBeExecutedTwice($db)
    {
        $gateway = $this->getRoleGateway($db);
        $group = $this->insertRole($gateway);
        $member = $this->insertRole($gateway);
        $gateway->addChild($group,$member)
                ->addChild($group,$member);
    }

    function testAllowHasAFluentInterface($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $user = $this->insertUser($this->getUserGateway($db));
        $test = $gateway->allow($user,$role->getName());
        $this->assertSame($gateway,$test);
    }

    function testGetByUserWithNoRolesAssociated($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $user1 = $this->insertUser($gw=$this->getUserGateway($db));
        $user2 = $this->insertUser($gw);
        $test = $gateway->allow($user1,$role->getName());
        $this->assertSame(array(),$gateway->getByUser($user2));
    }

    function testGetByUserWithSingleRoleAssociated($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $user1 = $this->insertUser($gw=$this->getUserGateway($db));
        $user2 = $this->insertUser($gw);
        $gateway->allow($user1,$role->getName());
        $i = 0;
        foreach($gateway->getByUser($user1) as $r) {
           $i++;
           $this->assertEquals($role,$r);
        }
        $this->assertSame(1,$i);
    }

    function testGetByUserWithMultipleRolesAssociated($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allow($user,$role1->getName())
                ->allow($user,$role2->getName());
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role1->getId()=>$role1,$role2->getId()=>$role2),$test);
    }

    function testGetByUserWithNestedRole($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $role3 = $this->insertRole($gateway);
        $this->insertRole($gateway); // unused role
        $gateway->addChild($role1,$role3);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allow($user,$role1->getName());
        $gateway->allow($user,$role2->getName());
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role1->getId()=>$role1,
                                  $role2->getId()=>$role2,
                                  $role3->getId()=>$role3), $test);
    }

    function testNoRoleRepetitionInGetByUserWithNestedRole($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $role3 = $this->insertRole($gateway);
        $gateway->addChild($role1,$role3);
        $gateway->addChild($role1,$role2);
        $gateway->addChild($role2,$role1);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allow($user,$role1->getName());
        $gateway->allow($user,$role2->getName());
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role1->getId()=>$role1,
                                  $role2->getId()=>$role2,
                                  $role3->getId()=>$role3), $test);
    }

    function testGetByUserWithDoubleNestedRole($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $role3 = $this->insertRole($gateway);
        $gateway->addChild($role1,$role2);
        $gateway->addChild($role2,$role3);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allow($user,$role1->getName());
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role1->getId()=>$role1,
                                  $role2->getId()=>$role2,
                                  $role3->getId()=>$role3), $test);
    }

    function testAllowAllHasAFluentInterface($db)
    {
        $gateway = $this->getRoleGateway($db);
        $user = $this->insertUser($this->getUserGateway($db));
        $test = $gateway->allowAll($user);
        $this->assertSame($gateway,$test);
    }

    function testAllowAllAssociatesMultipleRoles($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allowAll($user);
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role1->getId()=>$role1,$role2->getId()=>$role2),$test);
    }

    function testAllowAllWithSingleRole($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allowAll($user);
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role->getId()=>$role),$test);
    }

    function testAllowAllWithNoRoles($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allowAll($user);
        $test = $gateway->getByUser($user);
        $this->assertEquals(array(),$test);
    }

    function testDenyHasAFluentInterface($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role = $this->insertRole($gateway);
        $user = $this->insertUser($this->getUserGateway($db));
        $test = $gateway->deny($user,$role->getName());
        $this->assertSame($gateway,$test);
    }

    function testDenyRemovesPriviledge($db)
    {
        $this->deleteAll($db);
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $user = $this->insertUser($this->getUserGateway($db));
        $gateway->allowAll($user);
        $gateway->deny($user,$role1->getName());
        $test = $gateway->getByUser($user);
        $this->assertEquals(array($role2->getId()=>$role2),$test);
    }

    function testDenyAllHasAFluentInterface($db)
    {
        $gateway = $this->getRoleGateway($db);
        $user = $this->insertUser($this->getUserGateway($db));
        $test = $gateway->denyAll($user);
        $this->assertSame($gateway,$test);
    }

    function testDenyAllRemovesAllPriviledge($db)
    {
        $gateway = $this->getRoleGateway($db);
        $role1 = $this->insertRole($gateway);
        $role2 = $this->insertRole($gateway);
        $user1 = $this->insertUser($gw=$this->getUserGateway($db));
        $user2 = $this->insertUser($gw);
        $gateway->allowAll($user1);
        $gateway->allowAll($user2);
        $gateway->denyAll($user2);
        $this->assertSame(array(),$gateway->getByUser($user2));
        $this->assertTrue(count($gateway->getByUser($user1))>0);
    }

}
