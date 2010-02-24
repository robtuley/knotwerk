<?php
/**
 * Defines the class T_Unit_Case.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Unit test case.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
class T_Unit_Case implements T_CompositeLeaf, T_Unit_Test
{

    /**
     * Array of observers.
     *
     * @var T_Unit_Observer[]
     */
    protected $observers = array();

    /**
     * Observers which need to be triggered by this test case.
     *
     * @var T_Unit_Observer[]
     */
    protected $trigger = array();

    /**
     * Arguments to cycle on.
     *
     * @var array
     */
    protected $cycle = array();

    /**
     * Unit test factory.
     *
     * @var T_Unit_Factory
     */
    protected $factory = null;

    /**
     * Gets a factory.
     *
     * @return T_Unit_Factory
     */
    function getFactory()
    {
        return $this->factory;
    }

    /**
     * Sets the factory.
     *
     * @param T_Unit_Factory $factory
     * @return T_Unit_Case  fluent
     */
    function setFactory($factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Initialise factory.
     *
     * @return T_Unit_Case  fluent
     */
    function createDefaultFactory()
    {
        if (is_null($this->getFactory())) {
            $this->setFactory(new T_Unit_Factory());
        }
        return $this;
    }

    /**
     * Cycle over a test with a particular argument.
     *
     * @param string $name   argument name
     * @param array $data   data for this argument
     * @return T_Unit_Case   unit test case
     */
    protected function cycleOn($name,array $data)
    {
        $this->cycle[$name] = $data;
        return $this;
    }

    /**
     * Attach an observer to the test cases.
     *
     * @param T_Unit_Observer $observer  observer
     * @param bool $trigger  whether to trigger start/end events
     * @return T_Unit_Case  fluent interface
     */
    function attach(T_Unit_Observer $observer,$trigger=true)
    {
        $this->observers[] = $observer;
        if ($trigger) $this->trigger[] = $observer;
        return $this;
    }

    /**
     * Execute the unit test cases.
     *
     * @return T_Unit_Case  fluent interface
     */
    function execute()
    {
        $this->createDefaultFactory();
        foreach ($this->trigger as $observer) {
            $observer->init();
        }
        try {
            $this->setUpSuite();
            $this->executeTests();
            $this->tearDownSuite();
        } catch (T_Exception_SuiteSkip $skip) {
            foreach ($this->observers as $observer) {
                $observer->skip($skip,new ReflectionClass($this));
            }
        }
        foreach ($this->trigger as $observer) {
            $observer->complete();
        }
        return $this;
    }

    /**
     * Execute the actual tests.
     *
     * @return T_Unit_Case  fluent interface
     */
    protected function executeTests()
    {
        $reflect = new ReflectionObject($this);
        foreach ($reflect->getMethods() as $method) {
            if (!$method->isPublic() ||
                strncmp('test',$method->getName(),4)!==0) continue;

            // build a list of args to cycle on
            //   $arg[0] = array('arg1'=>'value','arg2'=>'value');
            //   $arg[1] = array('arg1'=>'value','arg2'=>'value');

            $args = array();

            // build args to cycle over
            $params = $method->getParameters();
            foreach ($params as $p) {
                $name = $p->getName();
                if (!isset($this->cycle[$name])) continue;
                $orig = $args;
                $args = array();
                foreach ($this->cycle[$name] as $val) {
                    if (count($orig)==0) {
                        $args[] = array($name=>$val);
                    } else {
                        foreach ($orig as $row) {
                            $row[$name] = $val;
                            $args[] = $row;
                        }
                    }
                }
            }

            // execute tests
            reset($args);
            $params = current($args);
            do {
                try {
                    $this->setUp();
                    if (is_array($params) && count($params)>0) {
                        $method->invokeArgs($this,$params);
                    } else {
                        $method->invoke($this);
                    }
                    foreach ($this->observers as $observe) {
                        $observe->pass($method);
                    }
                } catch (T_Exception_TestFail $fail) {
                    foreach ($this->observers as $observe) {
                        $observe->fail($fail,$method);
                    }
                } catch (T_Exception_TestSkip $skip) {
                    foreach ($this->observers as $observe) {
                        $observe->skip($skip,$method);
                    }
                } catch (Exception $error) {
                    foreach ($this->observers as $observe) {
                        $observe->error($error,$method);
                    }
                }
                $this->tearDown();
            } while($params = next($args));

        }
        return $this;
    }

    /**
     * Skip unit test.
     *
     * @param string $msg  reason for skipping
     */
    protected function skip($msg=null)
    {
        throw new T_Exception_TestSkip($msg);
    }

    /**
     * Skip all unit tests in this suite.
     *
     * @param string $msg  reason for skipping
     */
    protected function skipAll($msg=null)
    {
        throw new T_Exception_SuiteSkip($msg);
    }

    /**
     * Fail unit test.
     *
     * @param string $msg  reason for failure
     */
    protected function fail($msg=null)
    {
        throw new T_Exception_TestFail($msg);
    }

    /**
     * Setup any test fixtures.
     *
     * @return void
     */
    protected function setUp() { }

    /**
     * Tear down any test fixtures.
     *
     * @return void
     */
    protected function tearDown() { }

    /**
     * Setup suite.
     *
     * @return void
     */
    protected function setUpSuite() { }

    /**
     * Teardown suite.
     *
     * @return void
     */
    protected function tearDownSuite() { }

    /**
     * Returns the composite object or null.
     *
     * @return null  no composite
     */
    function getComposite()
    {
        return null;
    }

    /**
     * Creates a string to display from a value.
     *
     * @param mixed $value
     * @return string  display value
     */
    protected function display($value)
    {
        if (is_numeric($value)) {
            return (string) $value;
        } elseif (is_string($value)) {
            return $value;
        } elseif (is_object($value)) {
            return print_r($value,true);
        } elseif (is_resource($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            return print_r($value,true);
        } elseif (is_null($value)) {
            return 'NULL';
        }
        return (string) $value;
    }

    /**
     * Combine a user message with a comparison string.
     *
     * @param string $compare
     * @param string $msg
     * @return string
     */
    protected function combineUserMsg($compare,$msg)
    {
        if (strlen($msg)>0) {
            return "($msg) $compare";
        } else {
            return $compare;
        }
    }

    /**
     * Assert that two values are the same.
     *
     * @param mixed $expect  expected value
     * @param mixed $test  test value
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertSame($expect,$test,$msg=null)
    {
        // check the two types are the same
        if (gettype($expect)!==gettype($expect)) {
            $compare = 'Expected type '.gettype($expect).
                           ' is not the same as test type '.gettype($test);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        // compare the two values
        if ($expect !== $test) {
            $compare = $this->display($expect).' is not the same as '.$this->display($test);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that two values are the NOT the same.
     *
     * @param mixed $not_same_as  comparison value
     * @param mixed $test  test value
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     *
     */
    function assertNotSame($not_same_as,$test,$msg=null)
    {
        if ($not_same_as === $test) {
            $compare = $this->display($not_same_as).' is the same as '.$this->display($test);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that two values are equal.
     *
     * @param mixed $expect  expected value
     * @param mixed $test  test value
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertEquals($expect,$test,$msg=null)
    {
        if ($expect != $test) {
            $compare = $this->display($expect).' is not equal to '.$this->display($test);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that two values are NOT equal.
     *
     * @param mixed $not_equal_to  comparison value
     * @param mixed $test  test value
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertNotEquals($not_equal_to,$test,$msg=null)
    {
        if ($not_equal_to == $test) {
            $compare = $this->display($not_equal_to).' is equal to '.$this->display($test);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that a condition is true.
     *
     * @param bool $condition  test condition
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertTrue($condition,$msg=null)
    {
        if ($condition!==true) {
            $compare = $this->display($condition).' is not true';
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that a condition is false.
     *
     * @param bool $condition  test condition
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertFalse($condition,$msg=null)
    {
        if ($condition!==false) {
            $compare = $this->display($condition).' is not false';
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Whether a haystack contains a needle.
     *
     * @param mixed $needle
     * @param mixed $haystack
     * @return bool
     */
    protected function contains($needle,$haystack)
    {
        if (is_array($haystack) || ($haystack instanceof Iterator)) {
            $contains = false;
            foreach ($haystack as $test) {
                if ($needle===$test) $contains = true;
            }
        } else {
            $contains = (strpos((string) $haystack,(string) $needle)!==false);
        }
        return $contains;
    }

    /**
     * Assert that an array or string contains a value.
     *
     * @param mixed $needle  needle
     * @param mixed $haystack  haystack
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertContains($needle,$haystack,$msg=null)
    {
        if (!$this->contains($needle,$haystack)) {
            $compare = $this->display($haystack).' does not contain '.$this->display($needle);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that an array or string does NOT contain a value.
     *
     * @param mixed $needle  needle
     * @param mixed $haystack  haystack
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertNotContains($needle,$haystack,$msg=null)
    {
        if ($this->contains($needle,$haystack)) {
            $compare = $this->display($haystack).' does contain '.$this->display($needle);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Assert that two floats are similar to within a tolerance.
     *
     * @param float $expect  expected value
     * @param float $test  test value
     * @param float $tol  tolerance (fraction of expected diff)
     * @param string $msg  optional message
     * @return T_Unit_Case  fluent interface
     */
    function assertSimilarFloat($expect,$test,$tol=0.000001,$msg=null)
    {
        $diff = abs($expect-$test);
        if ($diff > abs($expect*$tol)) {
            $compare = $this->display($expect).' is not similar to '.$this->display($test);
            throw new T_Exception_AssertFail($this->combineUserMsg($compare,$msg));
        }
        return $this;
    }

    /**
     * Diff two strings.
     *
     * @see http://paulbutler.org/archives/a-simple-diff-algorithm-in-php/
     *      (C) Paul Butler 2007 <http://www.paulbutler.org/>
     *      (adapted)
     * @param string $expect
     * @param string $test
     * @param string $delimiter  defaults to space
     */
    function getDiff($expect,$test,$delimiter=' ')
    {
        $old = explode($delimiter,$expect);
        $new = explode($delimiter,$test);
        $diff = $this->diff($old,$new);

        // create human readable diff result
        $text = 'DIFF:'.EOL;
        foreach($diff as $k) {
            if(is_array($k)) {
                $text .= (!empty($k['d']) ? 'DELETE: '.implode($delimiter,$k['d']).' ' : '' ).
                         (!empty($k['i']) ? 'INSERT: '.implode($delimiter,$k['i']).' ' : '' );
                if ($text) $text .= EOL;
            }
        }
        return $text;
    }

    /**
     * Diff engine for two strings.
     *
     * @see http://paulbutler.org/archives/a-simple-diff-algorithm-in-php/
     *      (C) Paul Butler 2007 <http://www.paulbutler.org/>
     *      (adapted)
     * @param string $old
     * @param string $new
     */
    protected function diff($old,$new)
    {
        $maxlen = null;
        foreach($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                                            $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex]>$maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if($maxlen==0) return array(array('d'=>$old, 'i'=>$new));
        return array_merge(
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

}
