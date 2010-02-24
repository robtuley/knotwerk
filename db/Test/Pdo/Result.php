<?php
/**
 * Unit test cases for the T_Pdo_Result class.
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
class T_Test_Pdo_Result extends T_Unit_Case
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

    /**
     * Get SQL.
     *
     * @return string
     */
    protected function getCreateTableSql()
    {
        return "CREATE TABLE words (
                   id INTEGER PRIMARY KEY ASC, -- alias for sqlite rowid
                   word TEXT
                );
                INSERT INTO words (id,word)
                    VALUES (1,'a');
                INSERT INTO words (id,word)
                    VALUES (2,'b''b');
                INSERT INTO words (id,word)
                    VALUES (3,'Iñtërnâtiônàlizætiøn');
                INSERT INTO words (id,word)
                    VALUES (4,NULL)";
    }

    /**
     * Gets a zero row result.
     *
     * @return T_Pdo_Result
     */
    protected function getZeroRowResult()
    {
        $pdo = $this->getPopulatedDbConnection();
        $sql = 'SELECT id,word FROM words WHERE 1=2';
        return new T_Pdo_Result($pdo->query($sql));
    }

    protected function getSingleRowResult()
    {
        $pdo = $this->getPopulatedDbConnection();
        $sql = "SELECT id,word FROM words WHERE word='Iñtërnâtiônàlizætiøn'";
        return array(
                      'result' => new T_Pdo_Result($pdo->query($sql)),
                      'expect' => array('id'=>'3','word'=>'Iñtërnâtiônàlizætiøn')
                    );
    }

    protected function getMultipleRowResult()
    {
        $pdo = $this->getPopulatedDbConnection();
        $sql = "SELECT id,word FROM words ORDER BY id";
        return array(
                      'result' => new T_Pdo_Result($pdo->query($sql)),
                      'expect' => array(
                        array('id'=>'1','word'=>'a'),
                        array('id'=>'2','word'=>'b\'b'),
                        array('id'=>'3','word'=>'Iñtërnâtiônàlizætiøn'),
                        array('id'=>'4','word'=>null)
                    ) );
    }

    // Zero Row Results

    function testFetchAllGetsAnEmptyArrayWithZeroResult($result=false)
    {
        if (!$result) $result = $this->getZeroRowResult();
        $this->assertSame(array(),$result->fetchAll());
    }

    function testCountZeroResult($result=false)
    {
        if (!$result) $result = $this->getZeroRowResult();
        $this->assertSame(0,count($result));
    }

    function testFetchReturnsFalseWithZeroResult($result=false)
    {
        if (!$result) $result = $this->getZeroRowResult();
        $this->assertFalse($result->fetch());
    }

    function testNoIterationWithZeroResult($result=false)
    {
        if (!$result) $result = $this->getZeroRowResult();
        foreach ($result as $row) {
            $this->fail();
        }
    }

    function testZeroRowsCachedWithFetchAllFirst()
    {
        $result = $this->getZeroRowResult();
        $this->testFetchAllGetsAnEmptyArrayWithZeroResult($result);
        $this->testCountZeroResult($result);
        $this->testFetchReturnsFalseWithZeroResult($result);
        $this->testNoIterationWithZeroResult($result);
    }

    function testZeroRowsCachedWithCountFirst()
    {
        $result = $this->getZeroRowResult();
        $this->testCountZeroResult($result);
        $this->testFetchReturnsFalseWithZeroResult($result);
        $this->testNoIterationWithZeroResult($result);
        $this->testFetchAllGetsAnEmptyArrayWithZeroResult($result);
    }

    function testZeroRowsCachedWithFetchFirst()
    {
        $result = $this->getZeroRowResult();
        $this->testFetchReturnsFalseWithZeroResult($result);
        $this->testNoIterationWithZeroResult($result);
        $this->testFetchAllGetsAnEmptyArrayWithZeroResult($result);
        $this->testCountZeroResult($result);
    }

    function testZeroRowsCachedWithIterationFirst()
    {
        $result = $this->getZeroRowResult();
        $this->testNoIterationWithZeroResult($result);
        $this->testFetchAllGetsAnEmptyArrayWithZeroResult($result);
        $this->testCountZeroResult($result);
        $this->testFetchReturnsFalseWithZeroResult($result);
    }

    // Single Row Results

    function testFetchAllGetsArrayWithSingleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getSingleRowResult());
        $this->assertSame(array($expect),$result->fetchAll());
    }

    function testCountSingleResult($result=false)
    {
        if (!$result) extract($this->getSingleRowResult());
        $this->assertSame(1,count($result));
    }

    function testFetchReturnsArrayThenFalseWithSingleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getSingleRowResult());
        $this->assertSame($result->fetch(),$expect,'first row');
        $this->assertFalse($result->fetch());
    }

    function testSingleIterationWithSingleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getSingleRowResult());
        $i = 0;
        foreach ($result as $row) {
            $this->assertSame($row,$expect);
            $i++;
        }
        $this->assertSame(1,$i);
    }

    function testSingleRowCachedWithFetchAllFirst()
    {
        extract($this->getSingleRowResult());
        $this->testFetchAllGetsArrayWithSingleResult($result,$expect);
        $this->testCountSingleResult($result);
        $this->testFetchReturnsArrayThenFalseWithSingleResult($result,$expect);
        $this->testSingleIterationWithSingleResult($result,$expect);
    }

    function testSingleRowCachedWithCountFirst()
    {
        extract($this->getSingleRowResult());
        $this->testCountSingleResult($result);
        $this->testFetchReturnsArrayThenFalseWithSingleResult($result,$expect);
        $this->testSingleIterationWithSingleResult($result,$expect);
        $this->testFetchAllGetsArrayWithSingleResult($result,$expect);
    }

    function testSingleRowCachedWithFetchFirst()
    {
        extract($this->getSingleRowResult());
        $this->testFetchReturnsArrayThenFalseWithSingleResult($result,$expect);
        $this->testSingleIterationWithSingleResult($result,$expect);
        $this->testFetchAllGetsArrayWithSingleResult($result,$expect);
        $this->testCountSingleResult($result);
    }

    function testSingleRowCachedWithIterationFirst()
    {
        extract($this->getSingleRowResult());
        $this->testSingleIterationWithSingleResult($result,$expect);
        $this->testFetchAllGetsArrayWithSingleResult($result,$expect);
        $this->testCountSingleResult($result);
        $this->testFetchReturnsArrayThenFalseWithSingleResult($result,$expect);
    }

    // Single Row Results

    function testFetchAllGetsArrayWithMultipleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getMultipleRowResult());
        $this->assertSame($expect,$result->fetchAll());
    }

    function testCountMultipleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getMultipleRowResult());
        $this->assertSame(count($expect),count($result));
    }

    function testFetchReturnsArrayThenFalseWithMultipleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getMultipleRowResult());
        for ($i=0; $i<count($expect); $i++) {
            $this->assertSame($result->fetch(),$expect[$i],"row $i");
        }
        $this->assertFalse($result->fetch());
    }

    function testIterationWithMultipleResult($result=false,$expect=null)
    {
        if (!$result) extract($this->getMultipleRowResult());
        $i = 0;
        foreach ($result as $row) {
            $this->assertSame($row,$expect[$i],"row $i");
            $i++;
        }
        $this->assertSame(count($expect),$i);
    }

    function testMultipleRowCachedWithFetchAllFirst()
    {
        extract($this->getMultipleRowResult());
        $this->testFetchAllGetsArrayWithMultipleResult($result,$expect);
        $this->testCountMultipleResult($result,$expect);
        $this->testFetchReturnsArrayThenFalseWithMultipleResult($result,$expect);
        $this->testIterationWithMultipleResult($result,$expect);
    }

    function testMultipleRowCachedWithCountFirst()
    {
        extract($this->getMultipleRowResult());
        $this->testCountMultipleResult($result,$expect);
        $this->testFetchReturnsArrayThenFalseWithMultipleResult($result,$expect);
        $this->testIterationWithMultipleResult($result,$expect);
        $this->testFetchAllGetsArrayWithMultipleResult($result,$expect);
    }

    function testMultipleRowCachedWithFetchFirst()
    {
        extract($this->getMultipleRowResult());
        $this->testFetchReturnsArrayThenFalseWithMultipleResult($result,$expect);
        $this->testIterationWithMultipleResult($result,$expect);
        $this->testFetchAllGetsArrayWithMultipleResult($result,$expect);
        $this->testCountMultipleResult($result,$expect);
    }

    function testMultipleRowCachedWithIterationFirst()
    {
        extract($this->getMultipleRowResult());
        $this->testIterationWithMultipleResult($result,$expect);
        $this->testFetchAllGetsArrayWithMultipleResult($result,$expect);
        $this->testCountMultipleResult($result,$expect);
        $this->testFetchReturnsArrayThenFalseWithMultipleResult($result,$expect);
    }

}
