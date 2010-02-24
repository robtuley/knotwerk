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
class T_Test_Unit_Case extends T_Unit_Case
{

    function testSkipMethodThrowsSkipException()
    {
        $is_thrown = true;
        try {
            $this->skip();
            $is_thrown = false;
        } catch (T_Exception_TestSkip $skip) { }
        $this->assertTrue($is_thrown);
    }

    function testSkipMethodPassesMsgIntoException()
    {
        try {
            $this->skip('msg');
        } catch (T_Exception_TestSkip $skip) {
            $this->assertSame('msg',$skip->getMessage());
        }
    }

    function testFailMethodThrowsFailException()
    {
        $is_thrown = true;
        try {
            $this->fail();
            $is_thrown = false;
        } catch (T_Exception_TestFail $fail) { }
        $this->assertTrue($is_thrown);
    }

    function testFailMethodPassesMsgIntoException()
    {
        try {
            $this->fail('msg');
        } catch (T_Exception_TestFail $fail) {
            $this->assertSame('msg',$fail->getMessage());
        }
    }

    function testGetCompositeReturnsNull()
    {
        $this->assertSame(null,$this->getComposite());
    }

    /**
     * T_Unit_Case::assertSame()
     */

    function testAssertSameNoEffectWithIdenticalValues()
    {
        $this->assertSame(0,0);
        $this->assertSame(true,true);
        $this->assertSame(1.234,1.234);
        $this->assertSame('string','string');
        $this->assertSame(array(1,'ert'),array(1,'ert'));
        $obj = new T_Pattern_Regex('/test/');
        $this->assertSame($obj,$obj);
    }

    function testAssertSameFailsWhenTypesAreDifferent()
    {
        try {
            $this->assertSame(1,'1');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertSameFailsWhenValuesAreDifferent()
    {
        try {
            $this->assertSame(1,2);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertSameFailsWhenArraysInDifferentOrder()
    {
        try {
            $this->assertSame(array(0=>10,5=>14),array(5=>14,0=>10));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertSameFailsWhenObjectsAreDifferent()
    {
        $first = new T_Pattern_Regex('/test/');
        $second = new T_Pattern_Regex('/test/');
        try {
            $this->assertSame($first,$second);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertSameHasAFluentInterface()
    {
        $test = $this->assertSame(1,1);
        $this->assertSame($test,$this);
    }

    function testAssertSameIncludesErrorMessageInException()
    {
        try {
            $this->assertSame(4,7,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('4',$fail->getMessage());
            $this->assertContains('7',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertNotSame()
     */

    function testAssertNotSameNoEffectWithNonIdenticalValues()
    {
        $this->assertNotSame(0,'0');
        $this->assertNotSame(true,1);
        $this->assertNotSame(1.234,1.34);
        $this->assertNotSame('string','diff');
        $this->assertNotSame(array(1,'ert'),array('ert',1));
        $this->assertNotSame(new T_Pattern_Regex('/test/'),new T_Pattern_Regex('/test/'));
    }

    function testAssertNotSameFailsWhenIdenticalScalar()
    {
        try {
            $this->assertNotSame(1,1);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotSameFailsWhenIdenticalArrays()
    {
        try {
            $this->assertNotSame(array(0=>10,5=>14),array(0=>10,5=>14));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotSameFailsWhenObjectsAreSame()
    {
        $test = new T_Pattern_Regex('/test/');
        try {
            $this->assertNotSame($test,$test);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotSameHasAFluentInterface()
    {
        $test = $this->assertNotSame(1,2);
        $this->assertSame($test,$this);
    }

    function testAssertNotSameIncludesErrorMessageInException()
    {
        try {
            $this->assertNotSame(7,7,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('7',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertEquals()
     */

    function testAssertEqualsNoEffectWithSimilarValues()
    {
        $this->assertEquals(0,0);
        $this->assertEquals(true,1);
        $this->assertEquals(1.234,'1.234');
        $this->assertEquals('20a',20);
        $this->assertEquals(array(0=>1,1=>'ert'),array(1=>'ert',0=>1));
        $this->assertEquals(new T_Pattern_Regex('/test/'),new T_Pattern_Regex('/test/'));
    }

    function testAssertEqualsFailsWhenValuesAreDifferent()
    {
        try {
            $this->assertEquals(1,7);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertEqualsFailsWhenArraysAreDifferent()
    {
        try {
            $this->assertSame(array(0=>10,13=>14),array(5=>14,0=>10));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertEqualsFailsWhenObjectsAreDifferent()
    {
        $first = new T_Pattern_Regex('/test/');
        $second = new T_Pattern_Regex('/diff/');
        try {
            $this->assertEquals($first,$second);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertEqualsHasAFluentInterface()
    {
        $test = $this->assertEquals(1,1);
        $this->assertSame($test,$this);
    }

    function testAssertEqualsIncludesErrorMessageInException()
    {
        try {
            $this->assertEquals(4,7,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('4',$fail->getMessage());
            $this->assertContains('7',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertNotEquals()
     */

    function testAssertNotEqualsNoEffectWithNonIdenticalValues()
    {
        $this->assertNotEquals(0,'4');
        $this->assertNotEquals(false,10.34);
        $this->assertNotEquals(1.234,1.34);
        $this->assertNotEquals('string','diff');
        $this->assertNotEquals(array(1,'ert'),array('ert',1));
        $this->assertNotEquals(new T_Pattern_Regex('/test/'),new T_Pattern_Regex('/diff/'));
    }

    function testAssertNotEqualsFailsWhenEqualScalar()
    {
        try {
            $this->assertNotEquals(true,1);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotEqualsFailsWhenIdenticalArrays()
    {
        try {
            $this->assertNotEquals(array(0=>'10',5=>14),array(0=>10,5=>14));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotEqualsFailsWhenObjectsAreEqual()
    {
        try {
            $this->assertNotEquals(new T_Pattern_Regex('/test/'),new T_Pattern_Regex('/test/'));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotEqualsHasAFluentInterface()
    {
        $test = $this->assertNotEquals(1,2);
        $this->assertSame($test,$this);
    }

    function testAssertNotEqualsIncludesErrorMessageInException()
    {
        try {
            $this->assertNotEquals(7,7,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('7',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertTrue()
     */

    function testAssertTrueNoEffectWithBooleanTrue()
    {
        $this->assertTrue(true);
    }

    function testAssertTrueFailsWhenPassedFalse()
    {
        try {
            $this->assertTrue(false);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertTrueFailsWhenPassedANonBoolean()
    {
        try {
            $this->assertTrue(1);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertTrueHasAFluentInterface()
    {
        $test = $this->assertTrue(true);
        $this->assertSame($test,$this);
    }

    function testAssertTrueIncludesErrorMessageInException()
    {
        try {
            $this->assertTrue(7,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('7',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertFalse()
     */

    function testAssertFalseNoEffectWithBooleanFalse()
    {
        $this->assertFalse(False);
    }

    function testAssertFalseFailsWhenPassedTrue()
    {
        try {
            $this->assertFalse(true);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertFalseFailsWhenPassedANonBoolean()
    {
        try {
            $this->assertFalse(null);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertFalseHasAFluentInterface()
    {
        $test = $this->assertFalse(false);
        $this->assertSame($test,$this);
    }

    function testAssertFalseIncludesErrorMessageInException()
    {
        try {
            $this->assertFalse(7,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('7',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertContains()
     */

    function testAssertContainsNoEffectWithStringHaystackContainsScalarNeedle()
    {
        $this->assertContains('str','string');
        $this->assertContains('rin','string');
        $this->assertContains('ing','string');
        $this->assertContains('string','string');
        $this->assertContains(7,123456789);
        $this->assertContains(1.23,'I have 1.23 biscuits');
    }

    function testAssertContainsNoEffectWhenArrayHaystackContainsScalarNeedle()
    {
        $this->assertContains(7,array(7,8,9));
        $this->assertContains(8,array(7,8,9));
        $this->assertContains(9,array(7,8,9));
    }

    function testAssertContainsIsCaseSensitive()
    {
        try {
            $this->assertContains('sTr','string');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertContainsFailsWhenValuesAreNotIdentical()
    {
        try {
            $this->assertContains('7',array(7,8,9));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertContainsHasAFluentInterface()
    {
        $test = $this->assertContains('str','string');
        $this->assertSame($test,$this);
    }

    function testAssertContainsIncludesErrorMessageInException()
    {
        try {
            $this->assertContains('not','string','some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('not',$fail->getMessage());
            $this->assertContains('string',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertNotContains()
     */

    function testAssertNotContainsNoEffectWithStringHaystackNotContainsScalarNeedle()
    {
        $this->assertNotContains('not','string');
        $this->assertNotContains('sTr','string');
        $this->assertNotContains('stringy','string');
        $this->assertNotContains(0,123456789);
        $this->assertNotContains(1.23,'string');
    }

    function testAssertNotContainsNoEffectWhenArrayHaystackNotContainsScalarNeedle()
    {
        $this->assertNotContains('7',array(7,8,9));
        $this->assertNotContains(34,array(7,8,9));
    }

    function testAssertNotContainsFailsWhenDoesContainScalarinString()
    {
        try {
            $this->assertNotContains('str','string');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotContainsFailsArrayContainsIdenticalValue()
    {
        try {
            $this->assertNotContains(8,array(7,8,9));
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertNotContainsHasAFluentInterface()
    {
        $test = $this->assertNotContains('sTr','string');
        $this->assertSame($test,$this);
    }

    function testAssertNotContainsIncludesErrorMessageInException()
    {
        try {
            $this->assertNotContains('str','string','some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('str',$fail->getMessage());
            $this->assertContains('string',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::assertSimilarFloat()
     */

    function testAssertSimilarFloatNoEffectWithSameFloats()
    {
        $this->assertSimilarFloat(1.23,1.23);
        $this->assertSimilarFloat(123456789,123456789);
        $this->assertSimilarFloat(-1.23,-1.23);
        $this->assertSimilarFloat(0.000001,0.000001);
    }

    function testAssertSimilarFloatNoEffectWhenFloatsDifferentButWithinTolerance()
    {
        $this->assertSimilarFloat(1.23,1.24,0.01);
        $this->assertSimilarFloat(-1.23,-1.24,0.01);
        $this->assertSimilarFloat(123,124,0.01);
        $this->assertSimilarFloat(-123,-124,0.01);
    }

    function testAssertSimilarFloatFailsWhenNotInTol()
    {
        try {
            $this->assertSimilarFloat(1.23,1.24,0.001);
            $this->fail();
        } catch (T_Exception_AssertFail $fail) { }
    }

    function testAssertSimilarHasAFluentInterface()
    {
        $test = $this->assertSimilarFloat(1.23,1.23);
        $this->assertSame($test,$this);
    }

    function testAssertSimilarFloatIncludesErrorMessageInException()
    {
        try {
            $this->assertSimilarFloat(1.23,1.45,0.001,'some msg');
            $this->fail();
        } catch (T_Exception_AssertFail $fail) {
            $this->assertContains('some msg',$fail->getMessage());
            $this->assertContains('1.23',$fail->getMessage());
            $this->assertContains('1.45',$fail->getMessage());
        }
    }

    /**
     * T_Unit_Case::attach()
     */

    function testAttachMethodHasAFluentInterface()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $test = $case->attach($observer);
        $this->assertSame($test,$case);
    }

    function testAttachedObserverIsInitialisedByDefault()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer)->execute();
        $this->assertTrue($observer->isInit());
        $this->assertTrue($observer->isComplete());
    }

    function testAttachedObserverCanBeInitialisedExplicitally()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer,true)->execute();
        $this->assertTrue($observer->isInit());
        $this->assertTrue($observer->isComplete());
    }

    function testAttachedObserverInitialisationCanBeSuppressed()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer,false)->execute();
        $this->assertFalse($observer->isInit());
        $this->assertFalse($observer->isComplete());
    }

    function testMultipleAttachedObserverAreInitAsRequired()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer1 = new T_Test_Unit_ObserverStub();
        $observer2 = new T_Test_Unit_ObserverStub();
        $observer3 = new T_Test_Unit_ObserverStub();
        $observer4 = new T_Test_Unit_ObserverStub();
        $case->attach($observer1,false)
             ->attach($observer2)
             ->attach($observer3,false)
             ->attach($observer4)
             ->execute();
        $this->assertFalse($observer1->isInit());
        $this->assertFalse($observer1->isComplete());
        $this->assertTrue($observer2->isInit());
        $this->assertTrue($observer2->isComplete());
        $this->assertFalse($observer3->isInit());
        $this->assertFalse($observer3->isComplete());
        $this->assertTrue($observer4->isInit());
        $this->assertTrue($observer4->isComplete());
    }

    /**
     * Assert an observer method is called.
     *
     * @param string $type  pass, error, skip, fail
     * @param array $log
     */
    function assertObserverMethodCalled($type,$log,$num=1)
    {
        $called = 0;
        $map = array('pass'=>null,'fail'=>'T_Exception_TestFail',
                     'skip'=>'T_Exception_TestSkip','error'=>'Exception');
        foreach ($log as $call) {
            if ($call['method']===$type) {
                $called++;
                $method = new ReflectionMethod('T_Test_Unit_CaseStub','test'.ucfirst($type));
                $this->assertEquals($call['args'][0],$method);
                $class = $map[$type];
                if (!is_null($class)) {
                    $this->assertTrue($call['args'][1] instanceof $class);
                }
            }
        }
        $this->assertSame($num,$called);
    }

    function testPassResultsInSingleObserverPassMethodCalled()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer)->execute();
        $this->assertObserverMethodCalled('pass',$observer->getLog(),1);
    }

    function testSkipResultsInSingleObserverSkipMethodCalled()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer)->execute();
        $this->assertObserverMethodCalled('skip',$observer->getLog(),1);
    }

    function testFailResultsInSingleObserverFailMethodCalled()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer)->execute();
        $this->assertObserverMethodCalled('fail',$observer->getLog(),1);
    }

    function testErrorResultsInSingleObserverErrorMethodCalled()
    {
        $case = new T_Test_Unit_CaseStub();
        $observer = new T_Test_Unit_ObserverStub();
        $case->attach($observer)->execute();
        $this->assertObserverMethodCalled('error',$observer->getLog(),1);
    }

    /**
     * T_Unit_Case::execute()
     */

    function testExecuteMethodHasFluentInterface()
    {
        $case = new T_Test_Unit_CaseStub();
        $this->assertSame($case,$case->execute());
    }

    function testUnitTestCaseIsSetup()
    {
        $case = new T_Test_Unit_CaseStub();
        $this->assertTrue($case->execute()->isSetup());
    }

    function testUnitTestCaseIsTornDown()
    {
        $case = new T_Test_Unit_CaseStub();
        $this->assertTrue($case->execute()->isTearDown());
    }

    function testThatSetupIsExecutedForEachTest()
    {
        $case = new T_Test_Unit_CaseStub();
        $this->assertSame(4,$case->execute()->setup_count);
    }

    function testThatTeardownIsExecutedForEachTest()
    {
        $case = new T_Test_Unit_CaseStub();
        $this->assertSame(4,$case->execute()->teardown_count);
    }

}

/**
 * Unit test case stub.
 *
 * @package unitTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Unit_CaseStub extends T_Unit_Case implements T_Test_Stub
{

    protected $is_setup = false;
    protected $is_teardown = false;
    public $setup_count = 0;
    public $teardown_count = 0;

    function setUpSuite()
    {
        $this->is_setup = true;
    }

    function isSetUp()
    {
        return $this->is_setup;
    }

    function setUp()
    {
        $this->setup_count++;
    }

    function testPass() { }

    function testFail()
    {
        $this->fail();
    }

    function testSkip()
    {
        $this->skip();
    }

    function testError()
    {
        throw new Exception('test error');
    }

    function tearDown()
    {
        $this->teardown_count++;
    }

    function tearDownSuite()
    {
        $this->is_teardown = true;
    }

    function isTearDown()
    {
        return $this->is_teardown;
    }

}
