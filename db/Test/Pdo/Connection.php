<?php
class T_Test_Pdo_Connection extends T_Unit_Case
{

    function getConnection()
    {
        return new T_Pdo_Connection('sqlite::memory:');
    }

    function testCanOpenInMemoryDatabase()
    {
        $factory = $this->getConnection();
        $this->assertTrue($factory->connect() instanceof PDO);
        $factory->close();
    }

    function testConnectionIsOpenedOnlyOnce()
    {
        $factory = $this->getConnection();
        $this->assertSame($factory->connect(),$factory->connect());
        $factory->close();
    }

    function testNewConnectionIsCreatedIfOriginalIsClosed()
    {
        $factory = $this->getConnection();
        $one = $factory->connect();
        $factory->close();
        $two = $factory->connect();
        $this->assertNotSame($one,$two);
    }

    function testCloseMethodHasAFluentInterface()
    {
        $factory = $this->getConnection();
        $this->assertSame($factory,$factory->close());
    }

    function testConnectionFailureResultsInException()
    {
        try {
            $factory = new T_Pdo_Connection('notadsn');
            $factory->connect();
            $this->fail();
        } catch (T_Exception_Db $e) {
            $this->assertTrue(strlen($e->getMessage())>0);
        }
    }

    function testConnectionSetupToThrowExceptions()
    {
        $pdo = $this->getConnection()->connect();
        $this->assertSame($pdo->getAttribute(PDO::ATTR_ERRMODE),PDO::ERRMODE_EXCEPTION);
    }

    function testConnectionSetupToTreatZeroLengthStringsAsNulls()
    {
        $pdo = $this->getConnection()->connect();
        $this->assertSame($pdo->getAttribute(PDO::ATTR_ORACLE_NULLS),PDO::NULL_EMPTY_STRING);
    }

    function testCanGetConnectionName()
    {
        $pdo = $this->getConnection();
        $this->assertTrue(strlen($pdo->getName())>0);
    }

    function testCanFilterConnectionName()
    {
        $pdo = $this->getConnection();
        $f = new T_Test_Filter_Suffix('end');
        $this->assertSame($f->transform($pdo->getName()),$pdo->getName($f));
    }

}
