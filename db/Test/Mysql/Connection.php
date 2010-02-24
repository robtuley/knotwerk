<?php
class T_Test_Mysql_Connection extends T_Unit_Case
{

    protected $mysql;

    function setUpSuite()
    {
        $this->mysql = $this->getFactory()->getMysqlConnection();
    }

    function setUp()
    {
        if (!$this->mysql) {
            $this->skip('No MySQL connection available');
        }
    }

    function testConnectionIsOpenedOnlyOnce()
    {
        $this->assertSame($this->mysql->connect(),
                          $this->mysql->connect());
    }

    function testConnectionNameContainsMySQL()
    {
        $this->assertContains('MySQL',$this->mysql->getName());
    }

    function testCanFilterConnectionName()
    {
        $f = new T_Test_Filter_Suffix('end');
        $this->assertSame($f->transform($this->mysql->getName()),
                          $this->mysql->getName($f));
    }

    function testCanIdentifyConnectionAsMySQL()
    {
        $this->assertTrue($this->mysql->is(T_Db::MYSQL));
        $this->assertFalse($this->mysql->is(T_Db::SQLITE));
    }

}
