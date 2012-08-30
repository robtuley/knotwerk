<?php
class T_Test_Controller_Request extends T_Unit_Case
{

    protected function getDefaultServer()
    {
        $server['SERVER_NAME'] = 'example.com';
        $server['PHP_SELF'] = '/app/root/index'.T_PHP_EXT;
        $server['REQUEST_URI'] = '/app/root/some/path?name=value#anchor';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_METHOD'] = 'GET';
        return $server;
    }

    protected function getDefaultEnvironment($params = array())
    {
        $server = $this->getDefaultServer();
        foreach ($params as $name => $value) {
            $server[$name] = $value;
        }
        $input = array('SERVER'=>new T_Cage_Array($server));
        return new T_Test_EnvironmentStub($input);
    }

    protected function getRequest($env=null)
    {
        if (is_null($env)) $env = $this->getDefaultEnvironment();
        return new T_Controller_Request($env);
    }

    function testDefaultToNoSslConnection()
    {
        $request = $this->getRequest();
        $scheme = $request->getUrl()->getScheme();
        $this->assertSame($scheme,'http');
    }

    function testDetectSslConnectionWithPort443()
    {
        $env = $this->getDefaultEnvironment(array('SERVER_PORT'=>443));
        $request = $this->getRequest($env);
        $scheme = $request->getUrl()->getScheme();
        $this->assertSame($scheme,'https');
    }

    function testDetectSslConnectionWhenServerHttpsOn()
    {
        $env = $this->getDefaultEnvironment(array('HTTPS'=>'on'));
        $request = $this->getRequest($env);
        $scheme = $request->getUrl()->getScheme();
        $this->assertSame($scheme,'https');
    }

    function testDetectNoSslConnectionWhenServerHttpsOff()
    {
        $env = $this->getDefaultEnvironment(array('HTTPS'=>'off'));
        $request = $this->getRequest($env);
        $scheme = $request->getUrl()->getScheme();
        $this->assertSame($scheme,'http');
    }

    function testDetectSslConnectionWhenServerHttpsEqualOne()
    {
        $env = $this->getDefaultEnvironment(array('HTTPS'=>1));
        $request = $this->getRequest($env);;
        $scheme = $request->getUrl()->getScheme();
        $this->assertSame($scheme,'https');
    }

    function testDetectNoSslConnectionWhenServerHttpsEqualZero()
    {
        $env = $this->getDefaultEnvironment(array('HTTPS'=>0));
        $request = $this->getRequest($env);
        $scheme = $request->getUrl()->getScheme();
        $this->assertSame($scheme,'http');
    }

    function testHostNameComesFromServerName()
    {
        $request = $this->getRequest($env=$this->getDefaultEnvironment());
        $expect = $env->input('SERVER')->asScalar('SERVER_NAME')->uncage();
        $host = $request->getUrl()->getHost();
        $this->assertSame($host,$expect);
    }

    function testSubRootPathParsedIfNotZeroLength()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'/app/root/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/app/root/some/path',
                        )  );
        $request = $this->getRequest($env);
        $root = $request->getUrl()->getPath();
        $this->assertSame($root,array('app','root'));
    }

    function testSubRootPathParsedIfItIsZeroLength()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/some/path',
                        )  );
        $request = $this->getRequest($env);
        $root = $request->getUrl()->getPath();
        $this->assertSame($root,array());
    }

    function testRequestWithNoRootPathAndNoSubspace()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/',
                        )  );
        $request = $this->getRequest($env);
        $subspace = $request->getSubspace();
        $this->assertSame($subspace,array());
    }

    function testRequestWithNonZeroRootPathAndNoSubspace()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'/app/root/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/app/root/',
                        )  );
        $request = $this->getRequest($env);
        $subspace = $request->getSubspace();
        $this->assertSame($subspace,array());
    }

    function testRequestWithNoRootPathAndNonZeroSubspace()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/some/path/',
                        )  );
        $request = $this->getRequest($env);
        $subspace = $request->getSubspace();
        $this->assertSame($subspace,array('some','path'));
    }

    function testRequestWithNonZeroRootPathAndNonZeroSubspace()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'app/root/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/app/root/some/path/',
                        )  );
        $request = $this->getRequest($env);
        $subspace = $request->getSubspace();
        $this->assertSame($subspace,array('some','path'));
    }

    function testParametersOrAnchorDoesNotAffectSubspace()
    {
        $env = $this->getDefaultEnvironment(array(
                        'PHP_SELF'=>'/index'.T_PHP_EXT,
                        'REQUEST_URI'=>'/some/path/?name=value#anchor',
                        )  );
        $request = $this->getRequest($env);
        $subspace = $request->getSubspace();
        $this->assertSame($subspace,array('some','path'));
    }

    function testIllegalRequestMethodResponse()
    {
        $env = $this->getDefaultEnvironment(array(
                        'REQUEST_METHOD'=>'NOT_A_METHOD',
                        )  );
        try {
            $this->getRequest($env);
            $this->fail('no exception on root parse failure');
        } catch (T_Response $expected) {
            $this->assertSame($expected->getStatus(),405);
              // "Method Not Allowed" response.
        }
    }

    function testRequestIsAFactory()
    {
        $env = $this->getDefaultEnvironment();
        $request = $this->getRequest($env);
        $this->assertTrue($request instanceof T_Factory);
    }

    function testRequestLikeOperatesOnBaseFactory()
    {
        $env = $this->getDefaultEnvironment();
        $request = $this->getRequest($env);
        $this->assertTrue($request->like('T_Test_Filter_Suffix',array('suffix'=>'val'))
                          instanceof T_Test_Filter_Suffix);
    }

    function testRequestWillUseOperatesOnBaseFactory()
    {
        $env = $this->getDefaultEnvironment();
        $request = $this->getRequest($env);
        $this->assertSame($request,$request->willUse('T_Session_BlackHole'));
        $this->assertTrue($request->like('T_Session_Driver')
                          instanceof T_Session_BlackHole);
    }

    function testGetCoerceSchemeIsAlwaysNull()
    {
        $request = $this->getRequest();
        $this->assertTrue(is_null($request->getCoerceScheme()));
    }

    function testIsDelegatedIsAlwaysTrue()
    {
        $request = $this->getRequest();
        $this->assertTrue($request->isDelegated());
    }

}
