<?php
/**
 * Unit test cases for the T_User_Gateway class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_User_Gateway unit test cases.
 *
 * @package aclTests
 */
class T_Test_User_Gateway extends T_Unit_Case
{

    protected $dbs;

    function setUpSuite()
    {
        // setup DBs with necessary SQL
        $factory = $this->getFactory();
        $this->dbs = $factory->getAllDb();
        $factory->setupSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);

        // create gatways to cycle over
        $gateways = array();
        foreach ($this->dbs as $db) {
            $gateways[] = new T_User_Gateway($db,
                                             new T_Factory_Di());
        }
        $this->cycleOn('gw',$gateways);
    }

    function tearDownSuite()
    {
        $this->getFactory()
             ->teardownSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);
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

    function deleteAll()
    {
        foreach ($this->dbs as $db) {
            $db->master()->query('DELETE FROM person');
        }
    }

    function testThatInsertSaveCreatesId($gw)
    {
        $user = $this->createUser();
        $this->assertSame($gw,$gw->save($user),'fluent');
        $this->assertTrue(is_integer($user->getId()));
    }

    function testUserCanBeRetrievedById($gw)
    {
        $user = $this->insertUser($gw);
        $this->assertEquals($user,$gw->getById($user->getId()));
    }

    function testGetByIdFailsWhenNoUserFound($gw)
    {
        try {
            $gw->getById(-1);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testUserCanBeRetrievedByEmail($gw)
    {
        $user = $this->insertUser($gw);
        $this->assertEquals($user,$gw->getByEmail($user->getEmail()));
    }

    function testEmailComparisonIsCaseInsensitive($gw)
    {
        $user = $this->insertUser($gw);
        $test = $gw->getByEmail(strtoupper($user->getEmail()));
        $this->assertEquals($user,$test);
    }

    function testGetByEmailFailsWhenNoUserFound($gw)
    {
        try {
            $gw->getByEmail('notanemail');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testExistsByEmailReturnFalseWhenNoUser($gw)
    {
        $this->assertFalse($gw->existsByEmail('notanemail'));
    }

    function testExistsByEmailReturnTrueWhenIsUser($gw)
    {
        $user = $this->insertUser($gw);
        $this->assertTrue($gw->existsByEmail($user->getEmail()));
    }

    function testExistsByEmailIsCaseInsensitive($gw)
    {
        $user = $this->insertUser($gw);
        $this->assertTrue($gw->existsByEmail(strtoupper($user->getEmail())));
    }

    function testGetAllUsersReturnsEmptyArrayWithNoUsers($gw)
    {
        $this->deleteAll();
        $this->assertSame(array(),$gw->getAll());
    }

    function testGetAllUsersReturnsSingleUser($gw)
    {
        $this->deleteAll();
        $user = $this->insertUser($gw);
        $this->assertEquals(array($user->getId()=>$user),$gw->getAll());
    }

    function testGetAllUsersReturnsMultipleUsers($gw)
    {
        $this->deleteAll();
        $user1 = $this->insertUser($gw);
        $user2 = $this->insertUser($gw);
        $expect = array( $user1->getId() => $user1,
                         $user2->getId() => $user2
                          );
        $this->assertEquals($expect,$gw->getAll());
    }

    function testGetAllUsersOrdersByOptionalArgument($gw)
    {
        $this->deleteAll();
        $user1 = $this->insertUser($gw);
        $user2 = $this->insertUser($gw);
        $expect = array( $user2->getId() => $user2,
                         $user1->getId() => $user1
                          );
        $this->assertEquals($expect,$gw->getAll('id DESC'));
    }

    function testUserCanBeUpdated($gw)
    {
        $user1 = $this->insertUser($gw);
        $user2 = $this->insertUser($gw);
        $user1->setEmail('new\'email@example.com');
        $this->assertSame($gw,$gw->save($user1),'fluent');
        $this->assertEquals($user1,$gw->getById($user1->getId()));
        $this->assertEquals($user2,$gw->getById($user2->getId()));
    }

    function testDeleteRemovesUserFromDatabase($gw)
    {
        $user1 = $this->insertUser($gw);
        $user2 = $this->insertUser($gw);
        $this->assertSame($gw->delete($user1),$gw,'fluent');
        $this->assertFalse($gw->existsByEmail($user1->getEmail()));
        $this->assertTrue($gw->existsByEmail($user2->getEmail()));
    }

    function testDeleteHasNotEffectWhenUserNotInserted($gw)
    {
        $user = new T_User(null,'joe@example.com');
        $gw->delete($user);
    }

    function testDeleteSetsIdToNull($gw)
    {
        $user = $this->insertUser($gw);
        $gw->delete($user);
        $this->assertTrue(is_null($user->getId()));
    }

}
