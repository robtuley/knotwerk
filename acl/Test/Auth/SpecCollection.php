<?php
/**
 * Unit test cases for the T_Auth_SpecCollection class.
 *
 * @package aclTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Auth_SpecCollection unit test cases.
 *
 * @package aclTests
 */
class T_Test_Auth_SpecCollection extends T_Unit_Case
{

    function testBaseSpecOnItsOwnActsAsSingleAuthSpec()
    {
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $this->assertTrue($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(false));
        $this->assertFalse($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
    }

    function testBaseSpecCanBeSupplementedByAnAdditionalAndSpec()
    {
        // true, true
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $collection->andSpec(new T_Test_Auth_SpecStub(true));
        $this->assertTrue($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        // true, false
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $collection->andSpec(new T_Test_Auth_SpecStub(false));
        $this->assertFalse($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        // false, true
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(false));
        $collection->andSpec(new T_Test_Auth_SpecStub(true));
        $this->assertFalse($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        // false, false
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(false));
        $collection->andSpec(new T_Test_Auth_SpecStub(false));
        $this->assertFalse($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
    }

    function testAndSpecMethodHasAFluentInterface()
    {
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $test = $collection->andSpec(new T_Test_Auth_SpecStub(true));
        $this->assertSame($collection,$test);
    }

    function testBaseSpecCanBeSupplementedByAnAdditionalOrSpec()
    {
        // true, true
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $collection->orSpec(new T_Test_Auth_SpecStub(true));
        $this->assertTrue($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        // true, false
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $collection->orSpec(new T_Test_Auth_SpecStub(false));
        $this->assertTrue($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        // false, true
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(false));
        $collection->orSpec(new T_Test_Auth_SpecStub(true));
        $this->assertTrue($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
        // false, false
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(false));
        $collection->orSpec(new T_Test_Auth_SpecStub(false));
        $this->assertFalse($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
    }

    function testOrSpecMethodHasAFluentInterface()
    {
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $test = $collection->orSpec(new T_Test_Auth_SpecStub(true));
        $this->assertSame($collection,$test);
    }

    function testAdditionalOrAndSpecsCanBeCombinedRepeatedly()
    {
        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $collection->andSpec(new T_Test_Auth_SpecStub(true))
                   ->andSpec(new T_Test_Auth_SpecStub(false))
                   ->andSpec(new T_Test_Auth_SpecStub(true));
        $this->assertFalse($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));

        $collection = new T_Auth_SpecCollection(new T_Test_Auth_SpecStub(true));
        $collection->andSpec(new T_Test_Auth_SpecStub(false))
                   ->orSpec(new T_Test_Auth_SpecStub(true))
                   ->andSpec(new T_Test_Auth_SpecStub(true));
        $this->assertTrue($collection->isSatisfiedBy(new T_Auth(T_Auth::HUMAN)));
    }

}