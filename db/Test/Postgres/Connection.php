<?php
class T_Test_Postgres_Connection extends T_Unit_Case
{

    protected $pgsql;

    function setUpSuite()
    {
        $this->pgsql = $this->getFactory()->getPostgresConnection();
    }

    function setUp()
    {
        if (!$this->pgsql) {
            $this->skip('No PostgreSQL connection available');
        }
    }

    function testConnectionIsOpenedOnlyOnce()
    {
        $this->assertSame($this->pgsql->connect(),
                          $this->pgsql->connect());
    }

    function testConnectionNameContainsPostgresSQL()
    {
        $this->assertContains('PostgreSQL',$this->pgsql->getName());
    }

    function testCanFilterConnectionName()
    {
        $f = new T_Test_Filter_Suffix('end');
        $this->assertSame($f->transform($this->pgsql->getName()),
                          $this->pgsql->getName($f));
    }

    function testCanIdentifyConnectionAsMySQL()
    {
        $this->assertTrue($this->pgsql->is(T_Db::POSTGRES));
        $this->assertFalse($this->pgsql->is(T_Db::SQLITE));
    }

}
