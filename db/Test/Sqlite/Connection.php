<?php
/**
 * Unit test cases for the T_Sqlite_Connection class.
 *
 * @package dbTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Sqlite_Connection test cases.
 *
 * @package dbTests
 */
class T_Test_Sqlite_Connection extends T_Unit_Case
{

    function testCanOpenInMemoryDatabase()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $this->assertTrue($factory->connect() instanceof PDO);
        $factory->close();
    }

    function testConnectionIsOpenedOnlyOnce()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $this->assertSame($factory->connect(),$factory->connect());
        $factory->close();
    }

    function testNewConnectionIsCreatedIfOriginalIsClosed()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $one = $factory->connect();
        $factory->close();
        $two = $factory->connect();
        $this->assertNotSame($one,$two);
    }

    function testCloseMethodHasAFluentInterface()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $this->assertSame($factory,$factory->close());
    }

    function testConnectionNameContainsSQLite()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $this->assertContains('SQLite',$factory->getName());
    }

    function testCanFilterConnectionName()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $f = new T_Test_Filter_Suffix('end');
        $this->assertSame($f->transform($factory->getName()),$factory->getName($f));
    }

    function testCanIdentifyConnectionAsSQLite()
    {
        $factory = new T_Sqlite_Connection(':memory:');
        $this->assertFalse($factory->is(T_Db::MYSQL));
        $this->assertTrue($factory->is(T_Db::SQLITE));
    }

}
