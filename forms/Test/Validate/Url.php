<?php
/**
 * Unit test cases for the T_Validate_Url class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Url test cases.
 *
 * @package formTests
 */
class T_Test_Validate_Url extends T_Test_Filter_SkeletonHarness
{

    /**
     * Creates filter.
     *
     * @return T_Validate_Url
     */
    protected function getFilter()
    {
        return new T_Validate_Url();
    }

    /* Test *valid* emails are passed correctly */

    function testSchemeAndHostDotComNoSubdomain()
    {
        $url = new T_Url('http','example.com');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testDefaultSchemeIsHttpWithNoSubdomain()
    {
        $url = new T_Url('http','example.com');
        $this->assertEquals($url,$this->getFilter()->transform('example.com'));
    }

    function testDefaultSchemeIsHttpWithWwwSubdomain()
    {
        $url = new T_Url('http','www.example.com');
        $this->assertEquals($url,$this->getFilter()->transform('www.example.com'));
    }

    function testSchemeAndHostDotCoDotUkNoSubdomain()
    {
        $url = new T_Url('http','example.co.uk');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testSchemeAndHostWithWwwSubdomain()
    {
        $url = new T_Url('http','www.example.com');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testSchemeAndHostWithSubdomain()
    {
        $url = new T_Url('http','subdomain.example.co.uk');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testSchemeAndHostWithTrailingSlash()
    {
        $url = new T_Url('http','example.com');
        $this->assertEquals($url,$this->getFilter()->transform('http://example.com/'));
    }

    function testHostWithSslScheme()
    {
        $url = new T_Url('https','example.com');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testHostWithFtpScheme()
    {
        $url = new T_Url('ftp','example.com');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testNormalisesSchemeToLowerCase()
    {
        $url = new T_Url('http','example.com');
        $this->assertEquals($url,$this->getFilter()->transform('HTTP://example.com'));
    }

    function testNormalisesHostToLowerCase()
    {
        $url = new T_Url('http','example.com');
        $this->assertEquals($url,$this->getFilter()->transform('http://eXAMple.COM'));
    }

    function testSchemeAndHostWithExplicitPort()
    {
        $url = new T_Url('http','example.com:413');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testCastsExplicitPortToInteger()
    {
        $url = new T_Url('http','example.com:413');
        $this->assertEquals($url,$this->getFilter()->transform('http://example.com:413a'));
    }

    function testHostContainingDigitsAndDashes()
    {
        $url = new T_Url('http','ex-am23ple.com');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testSinglePathSegment()
    {
        $url = new T_Url('http','example.com',array('path'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testMultiplePathSegment()
    {
        $url = new T_Url('http','example.com',array('path','to','resource'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testPathWithTrailingSlash()
    {
        $url = new T_Url('http','example.com',array('some','path'));
        $str = 'http://example.com/some/path/';
        $this->assertEquals($url,$this->getFilter()->transform($str));
    }

    function testPathWithFilenameAtEnd()
    {
        $url = new T_Url('http','example.com',array('some','file.ext'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testUrlEncodedPathSegment()
    {
        $url = new T_Url('http','example.com',array('pa th','t&o','res%ou"rce'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testPathSegmentCanContainAPlusSign()
    {
        $url = new T_Url('http','example.com',array('pa th'));
        $str = 'http://example.com/pa+th/';
        $this->assertEquals($url,$this->getFilter()->transform($str));
    }

    function testSingleQueryParameter()
    {
        $url = new T_Url('http','example.com',array(),array('name'=>'value'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testMutipleQueryParameter()
    {
        $url = new T_Url('http','example.com',array(),array('a'=>'b','c'=>'d'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testUrlEncodedQueryParameters()
    {
        $url = new T_Url('http','example.com',array(),array('na@me'=>'val@ue'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testUrlQueryParameterContainingAMagicQuotedCharacter()
    {
        $url = new T_Url('http','example.com',array(),array('name'=>'val\'ue'));
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    function testUrlFragment()
    {
        $url = new T_Url('http','example.com',array(),array(),'anc2hor');
        $this->assertEquals($url,$this->getFilter()->transform($url->__toString()));
    }

    /* test invalid URL failures */

    function testInvalidUrlStrings()
    {
        $invalid = array( ' ',
                          'htp://example.com',             /* invalid schemes */
                          'fttp://example.com',
                          'notaurl',                       /* invalid domains */
                          'http://com',
                          'http://exam&ple.com',
                          'http://exam|ple.com',
                          'http://sub"domain.example.com',
                          'http://exam ple.com',
                          'http://example.co|m',
                          'http://example.toolong',
                          'http://example.com/in=valid/',    /* invalid paths */
                          'http://example.com/in;valid/',
                          'http://example.com/in"valid/',
                          'http://example.com/in valid/',
                          'http://example.com/in>valid/',
                          'http://example.com/in<valid/',
                          'http://example.com/in\\valid/',
               /* query string never fails validation: PHP does best to parse */
                          'http://example.com#in=valid',          /* fragment */
                          'http://example.com#in;valid',
                          'http://example.com#in"valid',
                          'http://example.com#in valid',
                          'http://example.com#in>valid',
                          'http://example.com#in<valid',
                          'http://example.com#in\\valid'
                           );
        $f = $this->getFilter();
        foreach ($invalid as $url) {
            try {
                $f->transform($url);
                $this->fail("$url passed validation.");
            } catch (T_Exception_Filter $e) { }
        }
    }



}