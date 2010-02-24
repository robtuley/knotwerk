<?php
class T_Test_Bootstrap extends T_Unit_Case
{

    // test global functions

    function testTransformActsOnFilterInputWhenAvailable()
    {
        $this->assertSame('test',_transform('test',null));
        $f = new T_Filter_RepeatableHash();
        $this->assertSame($f->transform('test'),_transform('test',$f));
    }

    function testCanPassTransformFunctionAFunctionName()
    {
        $this->assertSame('abc',_transform(' abc ','trim'));
    }

    function testCanPassArrayAsFilterCallback()
    {
        $f = new T_Filter_RepeatableHash();
        $this->assertSame($f->transform('test'),
                          _transform('test',array($f,'transform')));
    }

    function testFirstArrayMemberShortcut()
    {
        $this->assertSame(false,_first(array()));
        $this->assertSame('test',_first(array('test')));
        $this->assertSame('test',_first(array('test','more','members')));
    }

    function testLastArrayMemberShortcut()
    {
        $this->assertSame(false,_end(array()));
        $this->assertSame('test',_end(array('test')));
        $this->assertSame('test',_end(array('more','members','test')));
    }

    // tests HTTP environment

    protected function getDefaultServer()
    {
        $server['SERVER_NAME'] = 'example.com';
        $server['PHP_SELF'] = '/app/root/index'.T_PHP_EXT;
        $server['REQUEST_URI'] = '/app/root/some/path?name=value#anchor';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_METHOD'] = 'GET';
        return $server;
    }

    protected function getEnvStub(array $server)
    {
        return new T_Test_EnvironmentStub(array('SERVER'=>new T_Cage_Array($server)));
    }

    function testDefaultToNoSslConnection()
    {
        $server = $this->getDefaultServer();
        $env = $this->getEnvStub($server);
        $scheme = $env->getRequestUrl()->getScheme();
        $this->assertSame($scheme,'http');
    }

    function testDetectSslConnectionWithPort443()
    {
        $server = $this->getDefaultServer();
        $server['SERVER_PORT'] = '443';
        $env = $this->getEnvStub($server);
        $scheme = $env->getRequestUrl()->getScheme();
        $this->assertSame($scheme,'https');
    }

    function testDetectSslConnectionWhenServerHttpsOn()
    {
        $server = $this->getDefaultServer();
        $server['HTTPS'] = 'on';
        $env = $this->getEnvStub($server);
        $scheme = $env->getRequestUrl()->getScheme();
        $this->assertSame($scheme,'https');
    }

    function testDetectNoSslConnectionWhenServerHttpsOff()
    {
        $server = $this->getDefaultServer();
        $server['HTTPS'] = 'off';
        $env = $this->getEnvStub($server);
        $scheme = $env->getRequestUrl()->getScheme();
        $this->assertSame($scheme,'http');
    }

    function testDetectSslConnectionWhenServerHttpsEqualOne()
    {
        $server = $this->getDefaultServer();
        $server['HTTPS'] = 1;
        $env = $this->getEnvStub($server);
        $scheme = $env->getRequestUrl()->getScheme();
        $this->assertSame($scheme,'https');
    }

    function testDetectNoSslConnectionWhenServerHttpsEqualZero()
    {
        $server = $this->getDefaultServer();
        $server['HTTPS'] = 0;
        $env = $this->getEnvStub($server);
        $scheme = $env->getRequestUrl()->getScheme();
        $this->assertSame($scheme,'http');
    }

    function testHostNameComesFromServerName()
    {
        $server = $this->getDefaultServer();
        $expect = $server['SERVER_NAME'];
        $env = $this->getEnvStub($server);
        $host = $env->getRequestUrl()->getHost();
        $this->assertSame($host,$expect);
    }


    function testPathParsedIfNotZeroLength()
    {
        $server = $this->getDefaultServer();
        $server['REQUEST_URI'] = '/app/root/some/path';;
        $env = $this->getEnvStub($server);
        $root = $env->getRequestUrl()->getPath();
        $this->assertSame($root,array('app','root','some','path'));
    }

    function testPathParsedIfItIsZeroLength()
    {
        $server = $this->getDefaultServer();
        $server['REQUEST_URI'] = '/';
        $env = $this->getEnvStub($server);
        $path = $env->getRequestUrl()->getPath();
        $this->assertSame($path,array());
    }

    function testParametersOrAnchorDoesNotAffectPath()
    {
        $server = $this->getDefaultServer();
        $server['REQUEST_URI'] = '/some/path/?name=value#anchor';
        $env = $this->getEnvStub($server);
        $path = $env->getRequestUrl()->getPath();
        $this->assertSame($path,array('some','path'));
    }

    function testRequestMethodParsedFromServerSuperGlobal()
    {
        $server = $this->getDefaultServer();
        $server['REQUEST_METHOD'] = 'POST';
        $env = $this->getEnvStub($server);
        $method = $env->getMethod();
        $this->assertSame($method,'POST');
    }

    function testIsMethodReturnFalseWhenNotMatch()
    {
        $server = $this->getDefaultServer();
        $server['REQUEST_METHOD'] = 'POST';
        $env = $this->getEnvStub($server);
        $this->assertFalse($env->isMethod('GET'));
        $this->assertFalse($env->isMethod('PUT'));
        $this->assertFalse($env->isMethod('DELETE'));
    }

    function testIsAjaxReturnFalseWhenNotRequestedWithheaderOrNoMatchInHeader()
    {
        $server = $this->getDefaultServer();
        $env = $this->getEnvStub($server);
        $this->assertFalse($env->isAjax());

        $server['HTTP_X_REQUESTED_WITH'] = 'SomeThing';
        $env = $this->getEnvStub($server);
        $this->assertFalse($env->isAjax());
    }

    function testIsAjaxReturnTrueWhenRequestedWithHeaderMatches()
    {
        $server = $this->getDefaultServer();
        $server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $env = $this->getEnvStub($server);
        $this->assertTrue($env->isAjax());

        $server['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $env = $this->getEnvStub($server);
        $this->assertTrue($env->isAjax());
    }

    function testIsMethodReturnTrueWhenCaseInsensitiveNotMatch()
    {
        $server = $this->getDefaultServer();
        $server['REQUEST_METHOD'] = 'POST';
        $env = $this->getEnvStub($server);
        $this->assertTrue($env->isMethod('POST'));
        $this->assertTrue($env->isMethod('post'));
        $this->assertTrue($env->isMethod('pOsT'));
    }

    function testIfRunFromCommandLineUrlAndMethodSetToFalse()
    {
        $env = $this->getEnvStub(array());
        $this->assertFalse($env->getMethod());
        $this->assertFalse($env->getRequestUrl());
    }

    function testGetUrlReturnsClone()
    {
        $server = $this->getDefaultServer();
        $env = $this->getEnvStub($server);
        $url1 = $env->getRequestUrl();
        $url2 = $env->getRequestUrl();
        $this->assertEquals($url1,$url2);
        $this->assertNotSame($url1,$url2);
    }

}
