<?php
/**
 * Unit test cases for the T_Pdo_Master class.
 *
 * @package dbTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Pdo_Master test cases.
 *
 * @package dbTests
 */
class T_Test_Pdo_Master extends T_Test_Pdo_Slave
{

    /**
     * Get an empty DB.
     *
     * @return T_Pdo_Master
     */
    function getEmptyDb()
    {
        return new T_Pdo_Master(new PDO('sqlite::memory:'));
    }

    /**
     * Get a populated DB.
     *
     * @return T_Pdo_Master
     */
    protected function getPopulatedDb()
    {
        return new T_Pdo_Master($this->getPopulatedDbConnection());
    }

    // T_Pdo_Master::load()

    function testCanExecASingleWriteCommandWithLoad()
    {
        $db = $this->getPopulatedDb();
        $db->load("INSERT INTO words (word) VALUES ('z')");
        $result = $db->query("SELECT id FROM words WHERE word='z'");
        $this->assertSame(1,count($result));
    }

    function testCanExecMultiWriteCommandWithLoad()
    {
        $db = $this->getPopulatedDb();
        $db->load("INSERT INTO words (word) VALUES ('z');
                   INSERT INTO words (word) VALUES ('z');");
        $result = $db->query("SELECT id FROM words WHERE word='z'");
        $this->assertSame(2,count($result));
    }

    function testLoadProducesExceptionWithInvalidSingleQuery()
    {
        $db = $this->getEmptyDb();
        try {
            $db->load('this is not sql');
            $this->fail('accepts invalid sql');
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testLoadProducesExceptionWithInvalidLastQuery()
    {
        $db = $this->getEmptyDb();
        try {
            $db->load('CREATE TABLE test1 ( INT field ); '.
                      'this is not sql');
            $this->fail('accepts invalid sql');
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testLoadMethodHasAFluentInterface()
    {
        $db = $this->getEmptyDb();
        $test = $db->load('CREATE TABLE tablename ( INT field )');
        $this->assertSame($db,$test);
    }

    // T_Pdo_Master::query()  ... write queries

    function testWriteQueryWithQueryMethod()
    {
        $db = $this->getPopulatedDb();
        $test = $db->query("INSERT INTO words (word) VALUES ('z')");
        $this->assertSame($db,$test,'fluent interface with write query');
        $result = $db->query("SELECT id FROM words WHERE word='z'");
        $this->assertSame(1,count($result));
    }

    function testBoundWriteQueryWithQueryMethod()
    {
        $db = $this->getPopulatedDb();
        $test = $db->query("INSERT INTO words (word) VALUES (?)",array('z'));
        $this->assertSame($db,$test,'fluent interface with bound write query');
        $result = $db->query("SELECT id FROM words WHERE word='z'");
        $this->assertSame(1,count($result));
    }

    // T_Pdo_Master::getLastId()

    function testLastInsertIdReturnsNullBeforeAnyInsert()
    {
        $db = $this->getEmptyDb();
        $this->assertSame($db->getLastId(),null);
    }

    function testLastInsertIdReturnsLastInsert()
    {
        $db = $this->getPopulatedDb();
        $db->load("INSERT INTO words (word) VALUES ('z')");
        $id = $db->getLastId();
        $expect = (int) $db->queryAndFetch(
                    "SELECT id FROM words WHERE word='z'");
        $this->assertSame($id,$expect);
    }

    // T_Pdo_Master transaction methods

    function testIsCommittedReturnsTrueByDefault()
    {
        $this->assertTrue($this->getEmptyDb()->isCommitted());
    }

    function testBeginErrorProducesException()
    {
        $db = $this->getEmptyDb()->begin();
        try {
            $db->begin();
            $this->fail();
        } catch (T_Exception_Db $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testCommitErrorProducesException()
    {
        try {
            $this->getEmptyDb()->commit();
            $this->fail();
        } catch (T_Exception_Db $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testRollbackErrorProducesException()
    {
        try {
            $this->getEmptyDb()->rollback();
            $this->fail();
        } catch (T_Exception_Db $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testCanStartAndCommitATransaction()
    {
        $db = $this->getPopulatedDb();
        $this->assertSame($db,$db->begin(),'fluent');
        $this->assertFalse($db->isCommitted());
        $db->load("INSERT INTO words (word) VALUES ('z')");
        $this->assertSame($db,$db->commit(),'fluent');
        $this->assertTrue($db->isCommitted());
        $result = $db->query("SELECT id FROM words WHERE word='z'");
        $this->assertSame(1,count($result));
    }

    function testCanStartAndRollbackATransaction()
    {
        $db = $this->getPopulatedDb()->begin();
        $db->load("INSERT INTO words (word) VALUES ('z')");
        $this->assertSame($db,$db->rollback(),'fluent');
        $this->assertTrue($db->isCommitted());
        $result = $db->query("SELECT id FROM words WHERE word='z'");
        $this->assertSame(0,count($result));
    }

    function testCanAutoRollbackOnQueryException()
    {
        $db = $this->getPopulatedDb()->begin();
        $db->load("INSERT INTO words (word) VALUES ('z')");
        try {
            $db->load('not sql');
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue($db->isCommitted());
            $result = $db->query("SELECT id FROM words WHERE word='z'");
            $this->assertSame(0,count($result));
        }
    }

}
