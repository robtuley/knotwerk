<?php
class T_Test_Auth_IpHammer extends T_Unit_Case
{

    protected $dbs;

    function setUpSuite()
    {
        $factory = $this->getFactory();
        $this->dbs = $factory->getAllDb();
        $factory->teardownSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs)
                ->setupSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);
        $this->cycleOn('db',$this->dbs);
    }

    function tearDownSuite()
    {
        $this->getFactory()
             ->teardownSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);
    }

    function getAuth()
    {
        return new T_Auth(T_Auth::CHALLENGED,new T_User(23,'me@example.com'));
    }

    function getObserver($db,$ip='1.2.3.4')
    {
        $this->clearTable($db);
        $input = array();
        if ($ip) $input['SERVER']=new T_Cage_Array(array('REMOTE_ADDR'=>$ip));
        $env = new T_Test_EnvironmentStub($input);
        return new T_Auth_IpHammer($db,$env,3,30);
                                         // ^ tests assume threshold==3...
    }

    function clearTable($db)
    {
        $db->master()->query('DELETE FROM person_hammer_lock');
    }

    function expireAllLocks($db)
    {
        $db->master()->query('UPDATE person_hammer_lock SET expiry=?',
                             array(time()-20));
    }

    // edge cases

    function testPassWithNoPrevFailures($db)
    {
        $this->getObserver($db)->pass($this->getAuth());
    }

    function testFailWithNoPrevFailures($db)
    {
        $this->getObserver($db)->fail($this->getAuth());
    }

    function testFailThenPass($db)
    {
        $auth = $this->getAuth();
        $this->getObserver($db)->fail($auth)->pass($auth);
    }

    // behaviour

    function testConsecutiveFailuresOverThresholdFromOneIpHitLock($db)
    {
        $auth = $this->getAuth();
        $obs = $this->getObserver($db)->fail($auth)->fail($auth);
        try {
            $obs->fail($auth);
            $this->fail('First failure over threshold no error');
        } catch (T_Exception_Auth $e) {
           $this->assertTrue(strlen($e->getMessage())>0);
        }

        // either a further pass OR a fail result in locked
        try {
            $obs->fail($auth);
            $this->fail();
        } catch (T_Exception_Auth $e) {}
        try {
            $obs->pass($auth);
            $this->fail();
        } catch (T_Exception_Auth $e) {}
    }

    function testPassClearsQueueOfConsecutiveFailures($db)
    {
        $auth = $this->getAuth();
        $obs = $this->getObserver($db)->fail($auth)
                                      ->fail($auth)
                                      ->pass($auth)
                                      ->fail($auth)
                                      ->fail($auth)
                                      ->pass($auth)
                                      ->pass($auth);
    }

    function testExpiredLockGetsClearedOnNextFail($db)
    {
        $auth = $this->getAuth();
        $obs = $this->getObserver($db)->fail($auth)->fail($auth);
        try { $obs->fail($auth); } catch (T_Exception_Auth $e) {}
        $this->expireAllLocks($db);
        // expired lock, check is cleared
        $obs->fail($auth)->fail($auth);
        try {
            $obs->fail($auth);
            $this->fail();
        } catch (T_Exception_Auth $e) {}
    }

    function testExpiredLockGetsClearedOnNextPass($db)
    {
        $auth = $this->getAuth();
        $obs = $this->getObserver($db)->fail($auth)->fail($auth);
        try { $obs->fail($auth); } catch (T_Exception_Auth $e) {}
        $this->expireAllLocks($db);
        // expired lock, check is cleared
        $obs->pass($auth)->fail($auth)->fail($auth);
        try {
            $obs->fail($auth);
            $this->fail();
        } catch (T_Exception_Auth $e) {}
    }

    function testPassOrFailIgnoredWithNoIpAvailable($db)
    {
        $obs = $this->getObserver($db,null); // noIP
        $auth = $this->getAuth();
        $obs->fail($auth)->fail($auth)->fail($auth)->pass($auth);
    }

}