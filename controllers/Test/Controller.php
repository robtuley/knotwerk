<?php
class T_Test_Controller extends T_Unit_Case
{

    function getDefaultEnvironment($method='GET')
    {
        $env = new T_Test_EnvironmentStub(array());
        $env->setRequest(false,$method);
        return $env;
    }

    function getContext()
    {
        $env = $this->getDefaultEnvironment();
        $url = new T_Url('foo','example.com',array('some','path'));
        return new T_Test_Controller_ContextStub($env,$url,array('more'));
    }

    // -- Stub (internals) tests:

    function testUrlFromContextClonedNotReferenced()
    {
        $context = $this->getContext();
        $control = new T_Test_ControllerStub($context);
        $this->assertNotSame($control->getUrl(),$context->getUrl());
    }

    function testContextSubspaceReduced()
    {
        $context = $this->getContext();
        $expected = array_slice($context->getSubspace(),1);
        $control = new T_Test_ControllerStub($context);
        $this->assertSame($control->getSubspace(),$expected);
    }

    function testUrlHasBeenExpanded()
    {
        $context = $this->getContext();
        $expected = clone($context->getUrl());
        $ss = $context->getSubspace();
        $expected->appendPath(array_shift($ss));
        $control = new T_Test_ControllerStub($context);
        $this->assertEquals($control->getUrl(),$expected);
    }

    function testFailureDueToNoSubspaceInContext()
    {
        $context = $this->getContext();
        $context->setSubspace(array());
        try {
            $control = new T_Test_ControllerStub($context);
        } catch (T_Exception_Controller $expected) { }
    }

    function testControllerExecutesWhenNoNextController()
    {
        $context = $this->getContext();
        $control = new T_Test_ControllerStub($context);
        $this->assertFalse($control->isExecuted());
        $control->setFindNext(false);
        $response = new T_Test_ResponseStub();
        $control->handleRequest($response);
        $this->assertTrue($control->isExecuted());
    }

    function testControllerForwardsWhenNextControllerAvailable()
    {
        $context = $this->getContext();
        $control1 = new T_Test_ControllerStub($context);
        $control2 = new T_Test_ControllerStub($context);
        $control2->setFindNext(false);
        $control1->setFindNext($control2);
        $response = new T_Test_ResponseStub();
        $control1->handleRequest($response);
        // forwards from controller 1 --> controller 2
        $this->assertFalse($control1->isExecuted());
        $this->assertTrue($control2->isExecuted());
    }

    function testControllerActsAsAFactory()
    {
        $context = $this->getContext();
        $controller = new T_Test_ControllerStub($context);

        // test can get class with like
        $this->assertTrue($controller->like('T_Test_Filter_Suffix',array('suffix'=>'val'))
                          instanceof T_Test_Filter_Suffix);

        // can can 'use' items
        $this->assertSame($controller,$controller->willUse('T_Session_BlackHole'));
        $this->assertTrue($controller->like('T_Session_Driver')
                          instanceof T_Session_BlackHole);
    }

    function testExecuteDelegatesBasedOnRequestMethod()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller'));
        $control = new T_Test_ControllerStub($context);
        $control->setFindNext(false);
        $response = new T_Test_ResponseStub();
        $control->handleRequest($response);
        $this->assertTrue($control->isExecuted());
        $this->assertSame($control->isDelegatedTo(),
                          'GET' );
    }

    function testDefaultExecuteIsAbortAndThrowResponse()
    {
        $env = $this->getDefaultEnvironment('DELETE');
        $url = new T_Url('foo','example.com',array('some','path'));
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller'));
        $control = new T_Test_ControllerStub($context);
        $control->setFindNext(false);

        $response = new T_Test_ResponseStub();
        $filter = new T_Test_Response_FilterStub();
        $response->appendFilter($filter);

        try {
            $control->handleRequest($response);
            $this->fail();
        } catch (T_Response $expected) {
            $this->assertSame($expected->getStatus(),501);
            $this->assertTrue($filter->isPreFilteredAndAborted());
        }
    }

    function testControllerExecutesWithEmptySubspace()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller'));
        $control = new T_Test_ControllerStub($context);
          // findNext handled by actual class method
        $response = new T_Test_ResponseStub();
        $control->handleRequest($response);
        $this->assertTrue($control->isExecuted());
    }

    function testControllerForwardsToNextClassOnName()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller','T_Test_ControllerStub'));
        $control = new T_Test_ControllerStub($context);
          // findNext handled by actual class method
        $response = new T_Test_ResponseStub();
        $control->handleRequest($response);
        $this->assertFalse($control->isExecuted());
    }

    function testClassnameMappingFailResultsInAbortAndThrowResponse()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller','false'));
        $control = new T_Test_ControllerStub($context);
        $response = new T_Test_ResponseStub();
        $filter = new T_Test_Response_FilterStub();
        $response->appendFilter($filter);

        try {
            $control->handleRequest($response);
            $this->fail();
        } catch (T_Response $expected) {
            $this->assertSame($expected->getStatus(),404);
            $this->assertTrue($filter->isPreFilteredAndAborted());
        }
    }

    // -- actual T_Controller tests

    function testNoClassnameMappingByDefault()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('keep','going'));
        $control = new T_Controller($context);
        $response = new T_Test_ResponseStub();
        try {
            $control->handleRequest($response);
            $this->fail();
        } catch (T_Response $expected) {
            $this->assertSame($expected->getStatus(),404);
        }
    }

    function testAllRequestTypesResultInServerErrorByDefault()
    {
        $methods = array('GET','POST','HEAD','PUT','DELETE');
        foreach ($methods as $m) {
            $env = $this->getDefaultEnvironment($m);
            $url = new T_Url('foo','example.com',array('some','path'));
            $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller'));
            $control = new T_Controller($context);
            $response = new T_Test_ResponseStub();
            try {
                $control->handleRequest($response);
                $this->fail();
            } catch (T_Response $expected) {
                $this->assertSame($expected->getStatus(),501);
            }
        }
    }

    function testCanCoerceAndGetScheme()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('keep','going'));
        $control = new T_Controller($context);
        $control->coerceScheme('http');
        $this->assertSame('http',$control->getCoerceScheme());
    }

    function testCoerceSchemeHasAFluentInterface()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('keep','going'));
        $control = new T_Controller($context);
        $test = $control->coerceScheme('http');
        $this->assertSame($control,$test);
    }

    function testInheritsCoercedSchemeFromContext()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('keep','going'));
        $context->coerceScheme('https');
        $control = new T_Controller($context);
        $this->assertSame('https',$control->getCoerceScheme());
    }

    function testCanOverrideInheritedCoercedSchemeFromContext()
    {
        $url = new T_Url('foo','example.com',array('some','path'));
        $env = $this->getDefaultEnvironment();
        $context = new T_Test_Controller_ContextStub($env,$url,array('keep','going'));
        $context->coerceScheme('https');
        $control = new T_Controller($context);
        $control->coerceScheme('http');
        $this->assertSame('http',$control->getCoerceScheme());
    }

    function testControllerExecuteAbertsAndRedirectsIfNotCoercedScheme()
    {
        $env = $this->getDefaultEnvironment();
        $env->willUse(new T_Cage_Array(array()),'GET');
        $url = new T_Url('foo','example.com',array('some','path'));
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller'));
        $control = new T_Controller($context);
        $control->coerceScheme('https');
        $response = new T_Test_ResponseStub();
        try {
            $control->handleRequest($response);
        } catch (T_Response_Redirect $redirect) {
            $expect = clone $url;
            $expect->setScheme('https')
                   ->appendPath('thiscontroller');
            $f = new T_Filter_Xhtml();
            $this->assertContains($expect->getUrl($f),$redirect->getContent());
            $this->assertTrue($response->isAborted());
        }
    }

    function testControllerCoercedSchemeRedirectMaintainsGetParameters()
    {
        $env = $this->getDefaultEnvironment();
        $env->setInput('GET',
            new T_Cage_Array(array('name1'=>'value1',
                                   'name2'=>'value2'))
            );
        $url = new T_Url('foo','example.com',array('some','path'));
        $context = new T_Test_Controller_ContextStub($env,$url,array('thiscontroller'));
        $control = new T_Controller($context);
        $control->coerceScheme('https');
        $response = new T_Test_ResponseStub();
        try {
            $control->handleRequest($response);
        } catch (T_Response_Redirect $redirect) {
            $expect = clone $url;
            $expect->setScheme('https')
                   ->appendPath('thiscontroller')
                   ->setParameters($env->input('GET')->uncage());
            $f = new T_Filter_Xhtml();
            $this->assertContains($expect->getUrl($f),$redirect->getContent());
        }
    }
/* dispatcher tests
    function testDispatcherUrlSetToRequestUrl()
    {
        $request = new T_Controller_RequestContext();
        $dispatch = new T_Controller_Dispatch();
        $this->assertEquals($request->getUrl(),$dispatch->getUrl());
    }

    function testSubspaceSetToRequestSubspace()
    {
        $request = new T_Controller_RequestContext();
        $dispatch = new T_Controller_Dispatch();
        $this->assertEquals($request->getSubspace(),$dispatch->getSubspace());
    }

    function testDispatcherUrlIsSetAsServerAppRoot()
    {
        _as('SERVER')->setAppRoot(null);
        $request = new T_Controller_RequestContext();
        $dispatch = new T_Controller_Dispatch();
        $this->assertEquals($dispatch->getUrl(),_as('SERVER')->getAppRoot());
    }
*/
}
