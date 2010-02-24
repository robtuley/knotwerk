<?php
class T_Test_Auth_Cookie extends T_Unit_Case
{

    protected $dbs;

    function setUpSuite()
    {
        // setup DBs with necessary SQL
        $factory = $this->getFactory();
        $this->dbs = $factory->getAllDb();
        $factory->setupSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);

        $this->cycleOn('db',$this->dbs);
    }

    function tearDownSuite()
    {
        $this->getFactory()
             ->teardownSqlIn(T_ROOT_DIR.'acl/_sql/',$this->dbs);
    }

    function createCookie()
    {
        return new T_Test_Cage_CookieStub(array());
    }

    function getDriver($db,$cookie,$key='auth')
    {
        $env = new T_Test_EnvironmentStub(array('COOKIE'=>$cookie));
        return new T_Auth_Cookie($db,$env,$key);
    }

    function getUserGateway($db)
    {
        return new T_User_Gateway($db,new T_Factory_Di());
    }

    function getRoleGateway($db)
    {
        return new T_Role_Gateway($db,new T_Factory_Di());
    }

    function insertUser($db)
    {
        $gw = $this->getUserGateway($db);
        $user = new T_User(null,uniqid(rand(),true).'@example.com');
        $gw->save($user);
        return $user;
    }

    function testTypicalRememberMeLoginSystem($db)
    {
        // setup
        $user = $this->insertUser($db);
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');

        // save auth
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        $expiry = time()+2000;
        $this->assertSame($driver,$driver->save($auth,$expiry));

        // check cookie sent, and is a 32 length hash
        $this->assertSame(1,$cookie->getNumberCookiesSet());
        $this->assertTrue($cookie->exists('key'));
        $hash = $cookie->asScalar('key')->uncage();
        $this->assertSame(32,strlen($hash));
        $this->assertTrue(ctype_xdigit($hash));
        $args = $cookie->getSetCookieArgArray(0);
        $this->assertFalse($args['secure']);

        // now log back in using details
        $rgw = $this->getRoleGateway($db);
        $ugw = $this->getUserGateway($db);
        $auth = $driver->get($ugw,$rgw);

        // check that auth is correct
        $this->assertSame(T_Auth::TOKEN,$auth->getLevel());
        $this->assertEquals($user,$auth->getUser());
        $this->assertTrue($auth->getRole() instanceof T_Role_Queryable);

        // check that token has been re-issued
        $this->assertSame(2,$cookie->getNumberCookiesSet());
        $this->assertTrue($cookie->exists('key'));
        $hash2 = $cookie->asScalar('key')->uncage();
        $this->assertNotEquals($hash,$hash2);

        // check re-issued token can be re-used
        $auth = $driver->get($ugw,$rgw);
        $this->assertSame(T_Auth::TOKEN,$auth->getLevel());
        $this->assertEquals($user,$auth->getUser());
        $this->assertTrue($auth->getRole() instanceof T_Role_Queryable);
    }

    function testNoActionOnSaveIfNoUserOrExpiryInPast($db)
    {
        $user = $this->insertUser($db);
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');

        // no user in auth
        $expiry = time()+2000;
        $auth = new T_Auth(T_Auth::HUMAN);
        $this->assertSame($driver,$driver->save($auth,$expiry));
        $this->assertSame(0,$cookie->getNumberCookiesSet());

        // expiry in past
        $expiry = time()-2000;
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        $this->assertSame($driver,$driver->save($auth,$expiry));
        $this->assertSame(0,$cookie->getNumberCookiesSet());
    }

    function testTokenRefusedAndDeletedWhenExpired($db)
    {
        $user = $this->insertUser($db);
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');

        // save auth
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        $expiry = time()+2000;
        $this->assertSame($driver,$driver->save($auth,$expiry));
        $this->assertSame(1,$cookie->getNumberCookiesSet());

        // modify expiry
        $db->master()->query('UPDATE person_auth_token SET expiry='.(time()-200));

        // check auth refused
        $rgw = $this->getRoleGateway($db);
        $ugw = $this->getUserGateway($db);
        $auth = $driver->get($ugw,$rgw);
        $this->assertFalse($auth);
        $this->assertSame(2,$cookie->getNumberCookiesSet());
        $this->assertFalse($cookie->exists('key'));
           // ^ cookie overwritten
    }

    function testTokenRefusedAndDeletedWhenModified($db)
    {
        $user = $this->insertUser($db);
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');

        // save auth
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        $expiry = time()+2000;
        $this->assertSame($driver,$driver->save($auth,$expiry));
        $this->assertSame(1,$cookie->getNumberCookiesSet());

        // modify hash
        $cookie->set('key',md5(uniqid()));

        // check auth refused
        $rgw = $this->getRoleGateway($db);
        $ugw = $this->getUserGateway($db);
        $auth = $driver->get($ugw,$rgw);
        $this->assertFalse($auth);
        $this->assertSame(3,$cookie->getNumberCookiesSet());
        $this->assertFalse($cookie->exists('key'));
           // ^ cookie overwritten
    }

    function testNoAuthIfCookieDoesntExist($db)
    {
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');
        $rgw = $this->getRoleGateway($db);
        $ugw = $this->getUserGateway($db);
        $auth = $driver->get($ugw,$rgw);
        $this->assertFalse($auth);
    }

    function testCanSetAuthCookieAsHttpsOnly($db)
    {
        $user = $this->insertUser($db);
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');
        $this->assertSame($driver,$driver->setHttpsOnly());

        // login with 'remember me'
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        $expiry = time()+2000;
        $this->assertSame($driver,$driver->save($auth,$expiry));
        $this->assertSame(1,$cookie->getNumberCookiesSet());
        $args = $cookie->getSetCookieArgArray(0);
        $this->assertTrue($args['secure']);
    }

    function testCanExplicitallyDestroyCookieAuth($db)
    {
        $user = $this->insertUser($db);
        $driver = $this->getDriver($db,$cookie=$this->createCookie(),'key');

        // login with 'remember me'
        $auth = new T_Auth(T_Auth::CHALLENGED,$user);
        $expiry = time()+2000;
        $this->assertSame($driver,$driver->save($auth,$expiry));
        $this->assertSame(1,$cookie->getNumberCookiesSet());

        // explicitally logout
        $this->assertSame($driver,$driver->destroy());
        $this->assertSame(2,$cookie->getNumberCookiesSet());
        $this->assertFalse($cookie->exists('key'));
    }

}
