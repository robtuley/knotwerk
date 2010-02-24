<?php
class T_Test_Url_Xhtml extends T_Unit_Case
{

    protected $classname;

    function setUp()
    {
        parent::setUp();
        $this->classname = 'T_Url_Xhtml';
    }

    function testSchemeSetByConstructor()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getScheme(),'http');
    }

    function testHostSetByConstructor()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getHost(),'example.com');
    }

    function testNoPathSegmentsByDefault()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getPath(),array());
    }

    function testPathSetByConstructor()
    {
        $path = array('some','path');
        $url = new $this->classname('title','http','example.com',$path);
        $this->assertSame($url->getPath(),$path);
    }

    function testNoParametersByDefault()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getParameters(),array());
    }

    function testParametersSetByConstructor()
    {
        $parameters = array('name'=>'Rob','age'=>'25');
        $url = new $this->classname('title','http','example.com',array(),$parameters);
        $this->assertSame($url->getParameters(),$parameters);
    }

    function testNoFragmentByDefault()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertTrue(is_null($url->getFragment()));
    }

    function testFragmentSetByConstructor()
    {
        $url = new $this->classname('title','http','example.com',
                                     array(),array(),'anc');
        $this->assertSame($url->getFragment(),'anc');
    }

    function testTitleSetByConstructor()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getTitle(),'title');
    }

    function testTitleSetBySetTitleMethod()
    {
        $url = new $this->classname('title','http','example.com');
        $url->setTitle('diff title');
        $this->assertSame($url->getTitle(),'diff title');
    }

    // ATTRIBUTES

    function testSetAttributeAddsAnAttribute()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->setAttribute('name','value'),$url,'fluent');
        $this->assertSame($url->getAttribute('name'),'value');
        $this->assertSame($url->getAllAttributes(),array('name'=>'value'));
    }

    function testSetAttributeCanBeUsedToAddMultipleAttributes()
    {
        $url = new $this->classname('title','http','example.com');
        $url->setAttribute('name1','value1')
            ->setAttribute('name2','value2');
        $this->assertSame($url->getAttribute('name1'),'value1');
        $this->assertSame($url->getAttribute('name2'),'value2');
        $this->assertSame($url->getAllAttributes(),array('name1'=>'value1',
                                                         'name2'=>'value2')  );
    }

    function testSetAttributeCanOverwriteExistingAttributes()
    {
        $url = new $this->classname('title','http','example.com');
        $url->setAttribute('name','value1')
            ->setAttribute('name','value2');
        $this->assertSame($url->getAttribute('name'),'value2');
        $this->assertSame($url->getAllAttributes(),array('name'=>'value2'));
    }

    function testGetAllAttributesReturnsEmptyArrayByDefault()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getAllAttributes(),array());
    }

    function testGetAttributeReturnsNullIfNameNotSet()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getAttribute('name'),null);
    }

    // RENDERING

    function testBasicUrlRenderString()
    {
        $url = new $this->classname('title','foo','ex.com');
        $expected = '<a href="foo://ex.com">title</a>';
        $this->assertSame($url->__toString(),$expected);
    }

    function testEscapesUrlAndTitle()
    {
        $url = new $this->classname('ti&tle','foo','ex&ex.com');
        $expected = '<a href="foo://ex&amp;ex.com">ti&amp;tle</a>';
        $this->assertSame($url->__toString(),$expected);
    }

    // <a href="foo://ex.com" title="desc">title</a>
    function testBasicUrlRenderStringWithDescription()
    {
        $url = new $this->classname('title','foo','ex.com',array(),array(),null);
        $url->setAttribute('title','desc');
        $expected = '<a title="desc" href="foo://ex.com">title</a>';
        $this->assertSame($url->__toString(),$expected);
    }

    // <a href="foo://ex.com" accesskey="A">title</a>
    function testBasicUrlRenderStringWithAccessKey()
    {
        $url = new $this->classname('title','foo','ex.com',array(),array(),null);
        $url->setAttribute('accesskey','A');
        $expected = '<a accesskey="A" href="foo://ex.com">title</a>';
        $this->assertSame($url->__toString(),$expected);
    }

    function testRendersFullLinkDescriptionAndAccessKey()
    {
        $url = new $this->classname('title','foo','ex.com',array(),array(),null);
        $url->setAttribute('title','desc')
            ->setAttribute('accesskey','A');
        $expected = '<a title="desc" accesskey="A" href="foo://ex.com">title</a>';
        $this->assertSame($url->__toString(),$expected);
    }

    function testDescriptionIsEscaped()
    {
        $url = new $this->classname('title','foo','ex.com',array(),array(),null);
        $url->setAttribute('title','de&sc');
        $expected = '<a title="de&amp;sc" href="foo://ex.com">title</a>';
        $this->assertSame($url->__toString(),$expected);
    }

    function testCreateNewUrlByAppendPathCreatesNewInstance()
    {
        $url = new $this->classname('title','foo','ex.com');
        $test = $url->createByAppendPath('path','diff');
        $this->assertNotSame($url,$test);
        $this->assertSame($url->getPath(),array());
    }

    function testCreateNewUrlByAppendPathSameClassNameByDefault()
    {
        $url = new $this->classname('title','foo','ex.com');
        $test = $url->createByAppendPath('path','diff');
        $this->assertTrue($test instanceof $this->classname);
    }

    function testCreateNewUrlByAppendPathForceDifferentClassname()
    {
        $url = new $this->classname('title','foo','ex.com');
        $test = $url->createByAppendPath('path','diff','T_Url_Leaf');
        $this->assertTrue($test instanceof T_Url_Leaf);
    }

    function testCreateNewUrlByAppendPathAddsTitleAndAppendsPath()
    {
        $url = new $this->classname('title','foo','ex.com',array('some'));
        $test = $url->createByAppendPath('path','diff');
        $this->assertSame($test->getPath(),array('some','path'));
        $this->assertSame($test->getTitle(),'diff');
    }

    function testCreateNewUrlByAppendPathMaintainsSchemeAndHost()
    {
        $url = new $this->classname('title','foo','ex.com',array('some'));
        $test = $url->createByAppendPath('path','diff');
        $this->assertSame($test->getHost(),$url->getHost());
        $this->assertSame($test->getScheme(),$url->getScheme());
    }

    function testCreateNewUrlByAppendPathRemovesParametersFragment()
    {
        $url = new $this->classname('title','foo','ex.com',
                                    array('some'),array('name'=>'value'),
                                    'anchor');
        $test = $url->createByAppendPath('path','diff');
        $this->assertSame($test->getParameters(),array());
        $this->assertSame($test->getFragment(),null);
    }

    function testCreateNewUrlByFragmentCreatesNewInstance()
    {
        $url = new $this->classname('title','foo','ex.com');
        $test = $url->createByFragment('anchor','diff');
        $this->assertNotSame($url,$test);
        $this->assertSame($url->getFragment(),null);
    }

    function testCreateNewUrlByFragmentSameClassNameByDefault()
    {
        $url = new $this->classname('title','foo','ex.com');
        $test = $url->createByFragment('anchor','diff');
        $this->assertTrue($test instanceof $this->classname);
    }

    function testCreateNewUrlByFragmentForceDifferentClassname()
    {
        $url = new $this->classname('title','foo','ex.com');
        $test = $url->createByFragment('anchor','diff','T_Url_Leaf');
        $this->assertTrue($test instanceof T_Url_Leaf);
    }

    function testCreateNewUrlByFragmentAddsTitleAndFragment()
    {
        $url = new $this->classname('title','foo','ex.com',array('some'));
        $test = $url->createByFragment('anchor','diff');
        $this->assertSame($test->getFragment(),'anchor');
        $this->assertSame($test->getTitle(),'diff');
    }

    function testCreateNewUrlByFragmentMaintainsSchemeHostPath()
    {
        $url = new $this->classname('title','foo','ex.com',array('some'));
        $test = $url->createByFragment('path','diff');
        $this->assertSame($test->getHost(),$url->getHost());
        $this->assertSame($test->getScheme(),$url->getScheme());
        $this->assertSame($test->getPath(),$url->getPath());
    }

    function testCreateNewUrlByAppendingPathRemovesParametersFragment()
    {
        $url = new $this->classname('title','foo','ex.com',
                                    array('some'),array('name'=>'value'),
                                    'anchor');
        $test = $url->createByFragment('anchor','diff');
        $this->assertSame($test->getParameters(),array());
    }

    function testNoChangeFreqByDefault()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertTrue(is_null($url->getChangeFreq()));
    }

    function testChangeFreqSetBySetChangeFreqMethod()
    {
        $url = new $this->classname('title','http','example.com');
        $url->setChangeFreq('never');
        $this->assertSame($url->getChangeFreq(),'never');
    }

    function testChangeFreqCaseIsNormalised()
    {
        $url = new $this->classname('title','http','example.com');
        $url->setChangeFreq('nEvEr');
        $this->assertSame($url->getChangeFreq(),'never');
    }

    function testInvalidChangeFreqProducesException()
    {
        $url = new $this->classname('title','http','example.com');
        try {
            $url->setChangeFreq('notachangefreq');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testZeroPointFivePriorityByDefault()
    {
        $url = new $this->classname('title','http','example.com');
        $this->assertSame($url->getPriority(),0.5);
    }

    function testPrioritySetBySetPriorityMethod()
    {
        $url = new $this->classname('title','http','example.com');
        $url->setPriority(1.0);
        $this->assertSame($url->getPriority(),1.0);
    }

    function testInvalidPrioritySetProducesException()
    {
        $url = new $this->classname('title','http','example.com');
        try {
            $url->setPriority(1.1);
            $this->fail();
        } catch (InvalidArgumentException $e) {}
        try {
            $url->setPriority(-0.1);
            $this->fail();
        } catch (InvalidArgumentException $e) {}
    }

    function testSetTitleHasAFluentInterface()
    {
        $url = new $this->classname('title','http','example.com');
        $test = $url->setTitle('new title');
        $this->assertSame($url,$test);
    }

    function testSetChangeFreqHasAFluentInterface()
    {
        $url = new $this->classname('title','http','example.com');
        $test = $url->setChangeFreq('daily');
        $this->assertSame($url,$test);
    }

    function testSetPriorityHasAFluentInterface()
    {
        $url = new $this->classname('title','http','example.com');
        $test = $url->setPriority(0.7);
        $this->assertSame($url,$test);
    }

}
