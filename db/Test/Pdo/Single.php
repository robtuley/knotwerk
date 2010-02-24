<?php
/**
 * Unit test cases for the T_Pdo_Single class.
 *
 * @package dbTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Pdo_Single test cases.
 *
 * @package dbTests
 */
class T_Test_Pdo_Single extends T_Unit_Case
{

    /**
     * Gets test connection.
     *
     * @return T_Pdo_Single
     */
    protected function getConnection()
    {
        return new T_Pdo_Single(new T_Pdo_Connection('sqlite::memory:'));
    }

    function testGetMasterConnection()
    {
        $this->assertTrue($this->getConnection()->master()
                          instanceof T_Pdo_Master);
    }

    function testGetSlaveConnection()
    {
        $this->assertTrue($this->getConnection()->master()
                          instanceof T_Pdo_Master);
    }

    function testMaintainsSingleMaster()
    {
        $db = $this->getConnection();
        $this->assertSame($db->master(),$db->master());
    }

    function testMaintainsSingleSlave()
    {
        $db = $this->getConnection();
        $this->assertSame($db->slave(),$db->slave());
    }

    function testNewMasterAfterClose()
    {
        $db = $this->getConnection();
        $first = $db->master();
        $this->assertSame($db,$db->close());
        $this->assertNotSame($first,$db->master());
    }

    function testNewSlaveAfterClose()
    {
        $db = $this->getConnection();
        $first = $db->slave();
        $this->assertSame($db,$db->close());
        $this->assertNotSame($first,$db->slave());
    }

    function testCanGetConnectionName()
    {
        $db = $this->getConnection();
        $this->assertTrue(strlen($db->getName())>0);
    }

    function testCanFilterConnectionName()
    {
        $db = $this->getConnection();
        $f = new T_Test_Filter_Suffix('end');
        $this->assertSame($f->transform($db->getName()),$db->getName($f));
    }

    function testCanIdentifyConnectionAsSQLite()
    {
        $db = $this->getConnection();
        $this->assertFalse($db->is(T_Db::MYSQL));
        $this->assertTrue($db->is(T_Db::SQLITE));
    }

}
