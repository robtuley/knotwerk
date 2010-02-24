<?php
/**
 * Unit test cases for the T_Url class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Url unit test cases.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Url extends T_Unit_Case
{

    /**
     * URL object classname.
     *
     * @var string
     */
    protected $classname;

    /**
     * Standard test setup.
     */
    function setUp()
    {
        parent::setUp();
        $this->classname = 'T_Url';
    }

    function testSchemeSetByConstructor()
    {
        $url = new $this->classname('http','example.com');
        $this->assertSame($url->getScheme(),'http');
    }

    function testSchemeSetMethod()
    {
        $url = new $this->classname('http','example.com');
        $url->setScheme('ftp');
        $this->assertSame($url->getScheme(),'ftp');
    }

    function testHostSetByConstructor()
    {
        $url = new $this->classname('http','example.com');
        $this->assertSame($url->getHost(),'example.com');
    }

    function testHostSetMethod()
    {
        $url = new $this->classname('http','example.com');
        $url->setHost('www.example.com:25');
        $this->assertSame($url->getHost(),'www.example.com:25');
    }

    function testNoPathSegmentsByDefault()
    {
        $url = new $this->classname('http','example.com');
        $this->assertSame($url->getPath(),array());
    }

    function testPathSetByConstructor()
    {
        $path = array('some','path');
        $url = new $this->classname('http','example.com',$path);
        $this->assertSame($url->getPath(),$path);
    }

    function testPathSetMethodOverwritesExistingPath()
    {
        $path = array('some','path');
        $url = new $this->classname('http','example.com',array('otherpath'));
        $url->setPath($path);
        $this->assertSame($url->getPath(),$path);
    }

    function testPathAppendMethodAddsPathSegment()
    {
        $path = array('some','path');
        $url = new $this->classname('http','example.com',$path);
        $url->appendPath('more');
        $path[] = 'more';
        $this->assertSame($url->getPath(),$path);
    }

    function testNoParametersByDefault()
    {
        $url = new $this->classname('http','example.com');
        $this->assertSame($url->getParameters(),array());
    }

    function testParametersSetByConstructor()
    {
        $parameters = array('name'=>'Rob','age'=>'25');
        $url = new $this->classname('http','example.com',array(),$parameters);
        $this->assertSame($url->getParameters(),$parameters);
    }

    function testParameterSetMethodOverwritesExisting()
    {
        $parameters = array('name'=>'Rob','age'=>'25');
        $url = new $this->classname('http','example.com',array(),array('ex'));
        $url->setParameters($parameters);
        $this->assertSame($url->getParameters(),$parameters);
    }

    function testParameterAppendWithNewParameter()
    {
        $parameters = array('name'=>'Rob','age'=>'25');
        $url = new $this->classname('http','example.com',array(),$parameters);
        $url->appendParameter('weight',80);
        $parameters['weight'] = 80;
        $this->assertSame($url->getParameters(),$parameters);
    }

    function testParameterAppendWithExistingParameter()
    {
        $parameters = array('name'=>'Rob','age'=>'25');
        $url = new $this->classname('http','example.com',array(),$parameters);
        $url->appendParameter('name','Fred');
        $parameters['name'] = 'Fred';
        $this->assertSame($url->getParameters(),$parameters);
    }

    function testNoFragmentByDefault()
    {
        $url = new $this->classname('http','example.com');
        $this->assertTrue(is_null($url->getFragment()));
    }

    function testFragmentSetByConstructor()
    {
        $url = new $this->classname('http','example.com',array(),array(),'anc');
        $this->assertSame($url->getFragment(),'anc');
    }

    function testFragmentSetMethodOverwritesExisting()
    {
        $url = new $this->classname('http','example.com',array(),array(),'anc');
        $url->setFragment('another_anchor');
        $this->assertSame($url->getFragment(),'another_anchor');
    }

    // foo://example.com

    function testSchemeHostOnlyUrlString()
    {
        $url = new $this->classname('http','example.com');
        $this->assertSame($url->__toString(),'http://example.com');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'http://example.com/');
    }

    // foo://example.com/path

    function testSchemeHostWithSinglePathSegmentUrlString()
    {
        $url = new $this->classname('http','example.com',array('path'));
        $this->assertSame($url->__toString(),'http://example.com/path');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/path');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/path/');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'http://example.com/path/');
    }

    // foo://example.com/some/path

    function testSchemeHostWithMultiplePathSegmentsUrlString()
    {
        $url = new $this->classname('http','example.com',array('some','path'));
        $this->assertSame($url->__toString(),'http://example.com/some/path');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/some/path');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/some/path/');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'http://example.com/some/path/');
    }

    // foo://example.com?name=value

    function testSchemeHostWithSingleParameterUrlString()
    {
        $url = new $this->classname('http','example.com',array(),array('p'=>3));
        $this->assertSame($url->__toString(),'http://example.com?p=3');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/?p=3');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/?p=3');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'http://example.com/?p=3');
    }


    // foo://example.com?name=complex%36value

    function testParameterValueEncodedOnUrlWrite()
    {
        $params = array('name'=>'some&% complex$££value');
        $url = new $this->classname('http','example.com',array(),$params);
        $url_str = 'http://example.com?name='.rawurlencode($params['name']);
        $this->assertSame($url->__toString(),$url_str);
        $this->assertSame($url->getUrl(),$url->__toString());
    }

    // foo://example.com?complex%36name=value

    function testParameterNameEncodedOnUrlWrite()
    {
        $key = 'some&% complex$££name';
        $params = array($key=>'value');
        $url = new $this->classname('http','example.com',array(),$params);
        $url_str = 'http://example.com?'.rawurlencode($key).'=value';
        $this->assertSame($url->__toString(),$url_str);
        $this->assertSame($url->getUrl(),$url->__toString());
    }

    // foo://example.com?name1=value1&name2=value2

    function testSchemeHostWithMultipleParameterUrlString()
    {
        $params = array('p'=>3,'s'=>45);
        $url = new $this->classname('http','example.com',array(),$params);
        $this->assertSame($url->__toString(),'http://example.com?p=3&s=45');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/?p=3&s=45');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/?p=3&s=45');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'http://example.com/?p=3&s=45');
    }

    // foo://example.com/path?name=value

    function testSchemeHostPathAndParameterUrlString()
    {
        $url = new $this->classname('foo','ex.com',array('path'),array('p'=>3));
        $this->assertSame($url->__toString(),'foo://ex.com/path?p=3');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/path?p=3');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/path/?p=3');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'foo://ex.com/path/?p=3');
    }

    // foo://example.com#anc

    function testSchemeHostFragmentUrlString()
    {
        $url = new $this->classname('foo','ex.com',array(),array(),'anc');
        $this->assertSame($url->__toString(),'foo://ex.com#anc');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/#anc');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/#anc');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'foo://ex.com/#anc');
    }

    // foo://example.com/path#anc

    function testSchemeHostPathFragmentUrlString()
    {
        $url = new $this->classname('foo','ex.com',array('path'),array(),'anc');
        $this->assertSame($url->__toString(),'foo://ex.com/path#anc');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/path#anc');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/path/#anc');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'foo://ex.com/path/#anc');
    }

    // foo://example.com/path?name=value#anc

    function testSchemeHostPathParameterFragmentUrlString()
    {
        $url = new $this->classname('foo','ex.com',array('path'),
                                    array('p'=>3),'anc');
        $this->assertSame($url->__toString(),'foo://ex.com/path?p=3#anc');
        $this->assertSame($url->getUrl(),$url->__toString());
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST),'/path?p=3#anc');
        $this->assertSame($url->getUrl(null,T_Url::NO_HOST|T_Url::AS_DIR),'/path/?p=3#anc');
        $this->assertSame($url->getUrl(null,T_Url::AS_DIR),'foo://ex.com/path/?p=3#anc');
    }

    function testGetUrlAppliesDataFilter()
    {
        $url = new $this->classname('foo','ex.com');
        $f = new T_Test_Filter_Suffix();
        $this->assertSame($url->getUrl($f),$f->transform($url->getUrl()));
    }

    function testSchemeIsNormalisedToLowerCase()
    {
        $url = new $this->classname('FOO','example.com');
        $this->assertSame($url->__toString(),'foo://example.com');
    }

    function testHostIsNormalisedToLowerCase()
    {
        $url = new $this->classname('foo','EXAMPLE.COM');
        $this->assertSame($url->__toString(),'foo://example.com');
    }

    function testAnyTrailingSlashOnHostnameIsRemoved()
    {
        $url = new $this->classname('foo','example.com/');
        $this->assertSame($url->__toString(),'foo://example.com');
    }

    function testSinglePathElementIsUrlEncoded()
    {
        $url = new $this->classname('foo','example.com/',array('foo @+%/'));
        $this->assertSame($url->__toString(),'foo://example.com/foo%20%40%2B%25%2F');
    }

    function testMultiplePathElementIsUrlEncoded()
    {
        $url = new $this->classname('foo','example.com/',array('foo @','+%/'));
        $this->assertSame($url->__toString(),'foo://example.com/foo%20%40/%2B%25%2F');
    }

    function testFragmentIsUrlEncoded()
    {
        $url = new $this->classname('foo','example.com/',array(),array(),'foo @+%/');
        $this->assertSame($url->__toString(),'foo://example.com#foo%20%40%2B%25%2F');
    }

    function testSetSchemeHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->setScheme('https');
        $this->assertSame($url,$test);
    }

    function testSetHostHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->setHost('domain.com');
        $this->assertSame($url,$test);
    }

    function testAppendPathHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->appendPath('path');
        $this->assertSame($url,$test);
    }

    function testSetPathHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->setPath(array('path'));
        $this->assertSame($url,$test);
    }

    function testAppendParameterHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->appendParameter('name','value');
        $this->assertSame($url,$test);
    }

    function testSetParametersHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->setParameters(array('name','value'));
        $this->assertSame($url,$test);
    }

    function testSetFragmentHasAFluentInterface()
    {
        $url = new $this->classname('http','example.com');
        $test = $url->setFragment('anchor');
        $this->assertSame($url,$test);
    }

}