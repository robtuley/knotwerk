<?php
/**
 * Unit test cases for the T_Unit_Case class.
 *
 * @package unitTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Unit_Case unit test cases.
 *
 * @package unitTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Unit_Suite extends T_Unit_Case
{

    function testIsChildrenFalseWithNoChildren()
    {
        $suite = new T_Unit_Suite();
        $this->assertFalse($suite->isChildren());
    }

    function testAddSingleChild()
    {
        $suite = new T_Unit_Suite();
        $suite->addChild(new T_Test_Unit_CaseStub());
        $this->assertTrue($suite->isChildren());
    }

    function testGetComposite()
    {
        $suite = new T_Unit_Suite();
        $this->assertSame($suite,$suite->getComposite());
    }

    function testAddChildMethodHasAFluentInterface()
    {
        $suite = new T_Unit_Suite();
        $test = $suite->addChild(new T_Test_Unit_CaseStub());
        $this->assertSame($suite,$test);
    }

    /**
     * T_Unit_Suite::attach()
     */

    function testAttachMethodHasAFluentInterface()
    {
        $suite = new T_Unit_Suite();
        $observer = new T_Test_Unit_ObserverStub();
        $test = $suite->attach($observer);
        $this->assertSame($test,$suite);
    }

    function testAttachedObserverIsInitialisedByDefault()
    {
        $suite = new T_Unit_Suite();
        $observer = new T_Test_Unit_ObserverStub();
        $suite->attach($observer)->execute();
        $this->assertTrue($observer->isInit());
        $this->assertTrue($observer->isComplete());
    }

    function testAttachedObserverCanBeInitialisedExplicitally()
    {
        $suite = new T_Unit_Suite();
        $observer = new T_Test_Unit_ObserverStub();
        $suite->attach($observer,true)->execute();
        $this->assertTrue($observer->isInit());
        $this->assertTrue($observer->isComplete());
    }

    function testAttachedObserverInitialisationCanBeSuppressed()
    {
        $suite = new T_Unit_Suite();
        $observer = new T_Test_Unit_ObserverStub();
        $suite->attach($observer,false)->execute();
        $this->assertFalse($observer->isInit());
        $this->assertFalse($observer->isComplete());
    }

    function testAttachedObserverAreNotInitByChildren()
    {
        $suite = new T_Unit_Suite();
        $suite->addChild(new T_Test_Unit_CaseStub());
        $observer = new T_Test_Unit_ObserverStub();
        $suite->attach($observer,false)->execute();
        $this->assertFalse($observer->isInit());
        $this->assertFalse($observer->isComplete());
    }

    /**
     * T_Unit_Case::execute()
     */

    function testExecuteMethodHasFluentInterface()
    {
        $suite = new T_Unit_Suite();
        $this->assertSame($suite,$suite->execute());
    }

    function testExecuteMethodExecutesChildTests()
    {
        $suite = new T_Unit_Suite();
        $suite->addChild(new T_Test_Unit_CaseStub());
        $suite->addChild(new T_Test_Unit_CaseStub());
        $observer = new T_Test_Unit_ObserverStub();
        $suite->attach($observer);
        $suite->execute();
        $this->assertSame(8,count($observer->getLog()));
    }

}
