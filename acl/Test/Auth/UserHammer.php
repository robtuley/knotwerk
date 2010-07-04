<?php
class T_Test_Auth_UserHammer extends T_Unit_Case
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

    function getAuth($db)
    {
        $gw = new T_User_Gateway($db,new T_Factory_Di);
        $user = new T_User(null,uniqid().'@example.com');
        $gw->save($user);
        return new T_Auth(T_Auth::CHALLENGED,$user);
    }

    function getObserver($db)
    {
        $this->clearTable($db);
        return new T_Auth_UserHammer($db,3,30);
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
        $this->getObserver($db)->pass($this->getAuth($db));
    }

    function testFailWithNoPrevFailures($db)
    {
        $this->getObserver($db)->fail($this->getAuth($db));
    }

    function testFailThenPass($db)
    {
        $auth = $this->getAuth($db);
        $this->getObserver($db)->fail($auth)->pass($auth);
    }

    // behaviour

    function testConsecutiveFailuresOverThresholdFromOneIpHitLock($db)
    {
        $auth = $this->getAuth($db);
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
        $auth = $this->getAuth($db);
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
        $auth = $this->getAuth($db);
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
        $auth = $this->getAuth($db);
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
        $obs = $this->getObserver($db);
        $auth = new T_Auth(1,null);  // no user
        $obs->fail($auth)->fail($auth)->fail($auth)->pass($auth);
    }

}