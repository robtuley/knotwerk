<?php
class T_Test_Cage_Cookie extends T_Unit_Case
{

    function testDataSetInConstructor()
    {
        $data = array(1,'b'=>'a string');
        $cookie = new T_Cage_Cookie($data);
        $this->assertSame($data,$cookie->uncage());
    }


    function testCanDeleteCookie()
    {
        $cookie = new T_Test_Cage_CookieStub(array('key'=>'value'));
        $cookie->delete('key');
        $this->assertSame(array(),$cookie->uncage());
        $this->assertSame(1,$cookie->getNumberCookiesSet());
        $cookie = $cookie->getSetCookieArgArray(0);
        $this->assertSame($cookie['name'],'key');
        $this->assertTrue($cookie['expires']<time());
    }

    function testCookieSetPassesDataToSetCookie()
    {
        $cookie = new T_Test_Cage_CookieStub(array());
        $expiry = time()+60;
        $cookie->set('key','value',$expiry,'/some/path/','.domain.com',true);
        $this->assertSame(array( 'name'    => 'key',
                                 'value'   => 'value',
                                 'expires' => $expiry,
                                 'path'    => '/some/path/',
                                 'domain'  => '.domain.com',
                                 'secure'  => true ),
                            $cookie->getSetCookieArgArray(0)   );
    }

    function testCookieSetPopulatesCurrentCookieInstanceData()
    {
        $cookie = new T_Test_Cage_CookieStub(array());
        $cookie->set('key','value');
        $this->assertSame('value',$cookie->asScalar('key')->uncage());
    }

    function testCookieSetWithPassedExpiryResultsInNullCookieBeingSet()
    {
        $cookie = new T_Test_Cage_CookieStub(array('key'=>'value'));
        $expiry = time()-200;
        $cookie->set('key','anyvalue',$expiry);
        $this->assertSame(array( 'name'    => 'key',
                                 'value'   => null,
                                 'expires' => $expiry,
                                 'path'    => null,
                                 'domain'  => null,
                                 'secure'  => null ),
                            $cookie->getSetCookieArgArray(0)   );
        $this->assertFalse($cookie->exists('key'));
    }

    function testCookieSetHasAFluentInterface()
    {
        $cookie = new T_Test_Cage_CookieStub(array());
        $test = $cookie->set('key','value');
        $this->assertSame($cookie,$test);
    }

    function testCookieGivenCustomDomainMaintainedWhenServerAppRootIsSet()
    {
        $url = new T_Url('http','example.com',array('path'));
        $cookie = new T_Test_Cage_CookieStub(array());
	$this->assertSame($cookie,$cookie->setRootUrl($url));
        $cookie->set('key','value',null,null,'.domain.com');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame('.domain.com',$test['domain']);
        $this->assertSame(null,$test['path']);
    }

    function testCookieGivenCustomPathMaintainedWhenServerAppRootIsSet()
    {
        $url = new T_Url('http','example.com',array('path'));
        $cookie = new T_Test_Cage_CookieStub(array());
	$cookie->setRootUrl($url);
        $cookie->set('key','value',null,'/some/path/');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame(null,$test['domain']);
        $this->assertSame('/some/path/',$test['path']);
    }

    function testPathAndDomainSetFromServerAppRootByDefault()
    {
        $url = new T_Url('http','example.com',array('some','path'));
        $cookie = new T_Test_Cage_CookieStub(array());
	$cookie->setRootUrl($url);
        $cookie->set('key','value');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame('.example.com',$test['domain']);
        $this->assertSame('/some/path/',$test['path']);
    }

    function testDomainNotSetFromServerAppRootWhenIsTopLevelDomain()
    {
        $url = new T_Url('http','localhost',array('some','path'));
        $cookie = new T_Test_Cage_CookieStub(array());
	$cookie->setRootUrl($url);
        $cookie->set('key','value');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame(null,$test['domain']);
        $this->assertSame('/some/path/',$test['path']);
    }

    function testSetFailureWhenDomainIsTopLevelDomain()
    {
        $cookie = new T_Test_Cage_CookieStub(array());
        try {
            $cookie->set('key','value',null,null,'localhost');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('localhost',$e->getMessage());
        }
    }

    function testEmptyPathFromAppRootSetAsSingleForwardSlash()
    {
        $url = new T_Url('http','example.com',array());
        $cookie = new T_Test_Cage_CookieStub(array());
	$cookie->setRootUrl($url);
        $cookie->set('key','value');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame('.example.com',$test['domain']);
        $this->assertSame('/',$test['path']);
    }

    function testWwwPrefixStrippedFromAppRootDomain()
    {
        $url = new T_Url('http','www.example.com',array());
        $cookie = new T_Test_Cage_CookieStub(array());
	$cookie->setRootUrl($url);
        $cookie->set('key','value');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame('.example.com',$test['domain']);
    }

    function testAnyPortInformationStrippedFromAppRootDomain()
    {
        $url = new T_Url('http','www.example.com:413',array());
        $cookie = new T_Test_Cage_CookieStub(array());
	$cookie->setRootUrl($url);
        $cookie->set('key','value');
        $test = $cookie->getSetCookieArgArray(0);
        $this->assertSame('.example.com',$test['domain']);
    }

}
