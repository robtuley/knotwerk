<?php
/**
 * Unit test cases for the T_Pdo_Slave class.
 *
 * @package dbTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Pdo_Slave test cases.
 *
 * @package dbTests
 */
class T_Test_Pdo_Slave extends T_Unit_Case
{

    /**
     * Get populatd PDO.
     *
     * @return PDO
     */
    protected function getPopulatedDbConnection()
    {
        $factory = new T_Pdo_Connection('sqlite::memory:');
        $pdo = $factory->connect();
        $pdo->exec($this->getCreateTableSql());
        return $pdo;
    }

    protected function getCreateTableSql()
    {
        return "CREATE TABLE words (
                   id INTEGER PRIMARY KEY ASC, -- alias for sqlite rowid
                   word TEXT
                );
                INSERT INTO words (word)
                    VALUES ('a');
                INSERT INTO words (word)
                    VALUES ('bb');
                INSERT INTO words (word)
                    VALUES ('c''c');
                INSERT INTO words (word)
                    VALUES ('Iñtërnâtiônàlizætiøn');
                INSERT INTO words (word)
                    VALUES (NULL)";
    }

    /**
     * Get a populated DB.
     *
     * @return T_Pdo_Slave
     */
    protected function getPopulatedDb()
    {
        return new T_Pdo_Slave($this->getPopulatedDbConnection());
    }

    /**
     * Array of all rows.
     *
     * @return array
     */
    protected function getAllRows()
    {
        return array( array('word'=>'a'),
                      array('word'=>'bb'),
                      array('word'=>'c\'c'),
                      array('word'=>'Iñtërnâtiônàlizætiøn'),
                      array('word'=>null)
                     );
    }

    // T_Pdo_Slave::query()

    function testCanQueryToRetrieveMultipleRows()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query('SELECT word FROM words ORDER BY id');
        $this->assertSame($this->getAllRows(),$result->fetchAll());
    }

    function testCanQueryToRetrieveOneRow()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query('SELECT word FROM words ORDER BY id LIMIT 1');
        $this->assertSame(count($result),1);
        $this->assertSame($result->fetch(),_first($this->getAllRows()));
    }

    function testCanQueryToRetrieveZeroRows()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query("SELECT word FROM words WHERE word='notaword'");
        $this->assertSame(count($result),0);
        $this->assertSame($result->fetch(),false);
    }

    function testExceptionThrownWithInvalidQuery()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->query("not sql");
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testCanQueryWithSingleUnnamedParameter()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query("SELECT word FROM words WHERE word=?",array('bb'));
        $this->assertSame(count($result),1);
        $this->assertSame($result->fetch(),array('word'=>'bb'));
    }

    function testCanQueryWithSingleNamedParameter()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query("SELECT word FROM words WHERE word=:word",
                             array('word'=>'bb'));
        $this->assertSame(count($result),1);
        $this->assertSame($result->fetch(),array('word'=>'bb'));
    }

    function testBindedValuesAreEscapedAutomatically()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query("SELECT word FROM words WHERE word=?",array('c\'c'));
        $this->assertSame(count($result),1);
        $this->assertSame($result->fetch(),array('word'=>'c\'c'));
    }

    function testExceptionWithSqlErrorAndBoundParameters()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->query("not sql",array('bb'));
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testExceptionWhenSupplyTooManyParameters()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->query("SELECT word FROM words WHERE word=?",array('a','b'));
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testCanQueryWithMultipleBoundParameters()
    {
        $db = $this->getPopulatedDb();
        $result = $db->query("SELECT word FROM words WHERE word=? OR word=? ORDER BY id",
                             array('a','bb')  );
        $this->assertSame(count($result),2);
        $this->assertSame($result->fetch(),array('word'=>'a'));
        $this->assertSame($result->fetch(),array('word'=>'bb'));
        $this->assertSame($result->fetch(),false);
    }

    function testPreparedStatementsCanBeExecutedRepeatedlyWithCache()
    {
        $db = $this->getPopulatedDb();
        $words = array('Iñtërnâtiônàlizætiøn','a','bb','c\'c');
        foreach ($words as $w) {
            $result = $db->query("SELECT word FROM words WHERE word=:word",
                                 array('word'=>$w));
            $this->assertSame(count($result),1);
            $this->assertSame($result->fetch(),array('word'=>$w));
            $this->assertSame($result->fetch(),false);
        }
    }

    function testPreparedStatementsCanBeExecutedRepeatedlyWithoutCache()
    {
        $db = $this->getPopulatedDb();
        $this->assertSame($db,$db->disableCache(),'fluent');
        $words = array('Iñtërnâtiônàlizætiøn','a','bb','c\'c');
        foreach ($words as $w) {
            $result = $db->query("SELECT word FROM words WHERE word=:word",
                                 array('word'=>$w));
            $this->assertSame(count($result),1);
            $this->assertSame($result->fetch(),array('word'=>$w));
            $this->assertSame($result->fetch(),false);
        }
    }

    function testWriteQueryWithQueryMethod()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->query("INSERT INTO words (word) VALUES ('z')");
            $this->fail('accepts a write query');
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testBoundWriteQueryWithQueryMethod()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->query("INSERT INTO words (word) VALUES (?)",array('z'));
            $this->fail('accepts a bound write query');
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    // T_Pdo_Slave::queryAndFetch()

    function testCanQueryAndFetchSingleValue()
    {
        $db = $this->getPopulatedDb();
        $count = $db->queryAndFetch('SELECT COUNT(*) FROM words');
        $this->assertEquals($count,5);
    }

    function testCanQueryAndFetchToRetrieveSingleRow()
    {
        $db = $this->getPopulatedDb();
        $row = $db->queryAndFetch('SELECT id,word FROM words ORDER BY id LIMIT 1');
        $all = $this->getAllRows();
        $this->assertSame($row['word'],$all[0]['word']);
        $this->assertTrue(isset($row['id']));
    }

    function testExceptionThrownWithInvalidSqlQueryAndFetch()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->queryAndFetch("not sql");
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testExceptionThrownWhenTryToRetrievZeroRowsWithQueryAndFetch()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->queryAndFetch("SELECT * FROM words WHERE id=-23");
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testExceptionThrownWhenTryToRetrievMultipleRowsWithQueryAndFetch()
    {
        $db = $this->getPopulatedDb();
        try {
            $db->queryAndFetch("SELECT * FROM words");
            $this->fail();
        } catch (T_Exception_Query $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testCanUseBoundParametersWithQueryAndFetch()
    {
        $db = $this->getPopulatedDb();
        $word = $db->queryAndFetch("SELECT word FROM words WHERE word=?",array('c\'c'));
        $this->assertSame($word,'c\'c');
    }

    // T_Pdo_Slave::transform()

    function testDbCanBeUsedToEscapeValues()
    {
        $db = $this->getPopulatedDb();
        $this->assertSame("'a'",$db->transform('a'));
        $this->assertSame("'1'",$db->transform(1));
        $this->assertSame("NULL",$db->transform(null),'null -> NULL');
        $this->assertSame("NULL",$db->transform(''),'empty string -> NULL');
    }


}
