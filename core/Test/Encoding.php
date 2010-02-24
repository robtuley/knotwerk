<?php
/**
 * Unit test cases to check that the character encoding has been
 * setup correctly.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Character Encoding test cases.
 *
 * This class defines a series of unit tests that check the mbstring module has
 * been setup correctly and works OK under a language neutral UTF-8 regime.
 * Many of these settings must to set directly in the php.ini file which
 * restricts the portability of the library, but ensues it will work correctly
 * in an international context. Recommended settings in php.ini file are:
 *
 * <code>
 * mbstring.language = Neutral
 * mbstring.internal_encoding = UTF-8
 * ; leave input/output exactly as it is produced
 * mbstring.http_input = pass
 * mbstring.http_output = pass
 * mbstring.encoding_translation = Off
 * mbstring.detect_order = auto
 * mbstring.substitute_character = none
 * ; overload none of the operators (screws up binary strings)
 * mbstring.func_overload = 0
 * </code>
 *
 * Note that much of the testing in this routine comes from Harry Fuecks and
 * his PHP UTF8 project.
 *
 * @package coreTests
 * @see http://sourceforge.net/projects/phputf8
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Encoding extends T_Unit_Case
{

    function testMbInternalEncodingSet()
    {
        $this->assertSame(mb_internal_encoding(),T_CHARSET);
    }

    function testMbLanguageSetToUnicode()
    {
        $this->assertSame('uni',mb_language());
    }

    function testStringFunctionsNotOverloaded()
    {
        $utf8 = 'Iñtërnâtiônàlizætiøn';
        // binary length == 27, actual length == 20
        $this->assertSame(strlen($utf8),27);
        $this->assertSame(mb_strlen($utf8),20);
    }

    function testInternalEncodingUtf8()
    {
        $this->assertSame('UTF-8',mb_internal_encoding());
        $this->assertSame(T_CHARSET,mb_internal_encoding());
    }

    function testMbStringSetup()
    {
        $setup = mb_get_info();
        $this->assertSame($setup['http_output'],'pass');
        if (isset($setup['encoding_translation'])) {
            $this->assertSame($setup['encoding_translation'],'Off');
        }
    }

    function testMbIsAscii()
    {
        $this->assertTrue(mb_is_ascii('ascii string '));
        $this->assertFalse(mb_is_ascii('Iñtërnâtiônàlizætiøn'));
    }

    function testMbReduceToAscii()
    {
        $ascii = '  ascii786  ';
        $utf8  = ' Iñtërnâtiônàlizætiøn ';
        $this->assertSame(mb_reduce_to_ascii($ascii),$ascii);
        $this->assertSame(mb_reduce_to_ascii($utf8),' Itrntinliztin ');
    }

    function testMbSubstrReplaceWithAscii()
    {
        $str = 'Ascii';
        $this->assertSame(mb_substr_replace($str,'ñ',1),'Añscii'); // no len
        $this->assertSame(mb_substr_replace($str,'ñ',0,2),'ñcii');
        $this->assertSame(mb_substr_replace($str,'ñx',2,3),'Asñx');
        $this->assertSame(mb_substr_replace($str,'z',2,10),'Asz'); // end+
        $this->assertSame(mb_substr_replace($str,'ñ',1,2),'Añii');
    }

    function testMbSubstrReplaceWithUtf8()
    {
        $str = 'âønæë';
        $this->assertSame(mb_substr_replace($str,'ñ',1),'âñønæë'); // no len
        $this->assertSame(mb_substr_replace($str,'ñ',0,2),'ñnæë');
        $this->assertSame(mb_substr_replace($str,'ñx',2,3),'âøñx');
        $this->assertSame(mb_substr_replace($str,'z',2,10),'âøz'); // end+
        $this->assertSame(mb_substr_replace($str,'ñ',1,2),'âñæë');
    }

    function testMbStrCaseCmpWithAscii()
    {
        $compare = array ( 'as' => 'AS', 'AcY' => 'acy' ,
                           'diff' => 'erent', 'a' => 'a');
        foreach ($compare as $str1 => $str2) {
        	$this->assertSame(mb_strcasecmp($str1,$str2),
        	                    strcasecmp($str1,$str2)     );
        }
    }

    function testMbStrCaseCmpWithMatching()
    {
        $same = array( 'iñtërnâtiônàlizætiøn' => 'IÑTËRNÂTIÔNÀLIZÆTIØN',
                       'iñtërNÂtiônàlizætiøn' => 'IÑTËRnâTIÔNÀLIZÆTIØN',
                       "iñtërnâtiôn\nàlizætiøn" => "IÑTËRNÂTIÔN\nÀLIZÆTIØN");
        foreach ($same as $str1 => $str2) {
        	$this->assertSame(mb_strcasecmp($str1,$str2),0);
        }
        $this->assertSame(mb_strcasecmp('',''),0);  // empty
    }

    function testMbStrCaseCmpWithNotMatching()
    {
        $diff = array( 'iñtërnâtiônlizætiøn' => 'IÑTËRNÂTIÔNÀLIZÆTIØN',
                       'iñtërNÂtiônàlizætiøn' => 'IÑTËRnâTIÔNLIZÆTIØN',
                       'iñtërNÂtiônàlizætiøn' => '');
        foreach ($diff as $str1 => $str2) {
        	$this->assertNotEquals(mb_strcasecmp($str1,$str2),0);
        }
    }

    function testMbStristrWithSubStrMatch()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = 'NÂT';
        $this->assertSame(mb_stristr($str,$search),'nâtiônàlizætiøn');
    }

    function testMbStristrWithNoMatch()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = 'foo';
        $this->assertFalse(mb_stristr($str,$search));
    }

    function testUcfirst()
    {
        $str = 'ñtërnâtiônàlizætiøn';
        $ucfirst = 'Ñtërnâtiônàlizætiøn';
        $this->assertSame(mb_ucfirst($str),$ucfirst);
    }

    function testUcfirstSpace()
    {
        $str = ' iñtërnâtiônàlizætiøn';
        $ucfirst = ' iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_ucfirst($str),$ucfirst);
    }

    function testUcfirstUpper()
    {
        $str = 'Ñtërnâtiônàlizætiøn';
        $ucfirst = 'Ñtërnâtiônàlizætiøn';
        $this->assertSame(mb_ucfirst($str),$ucfirst);
    }

    function testUcfirstEmptyString()
    {
        $str = '';
        $this->assertSame(mb_ucfirst($str),'');
    }

    function testUcfirstOneChar()
    {
        $str = 'ñ';
        $ucfirst = 'Ñ';
        $this->assertSame(mb_ucfirst($str),$ucfirst);
    }

    function testMbStrIreplaceReplace()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlisetiøn';
        $this->assertSame(mb_str_ireplace('lIzÆ','lise',$str),$replaced);
    }

    function testMbStrIreplaceReplaceContainingHash()
    {
        $str = 'Iñtër#âtiô#àlizætiø#';
        $replaced = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_str_ireplace('#','n',$str),$replaced);
    }

    function testMbStrIreplaceReplaceNoMatch()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_str_ireplace('foo','bar',$str),$str);
    }

    function testMbStrIreplaceEmptyString()
    {
        $str = '';
        $this->assertSame(mb_str_ireplace('foo','bar',$str),$str);
    }

    function testMbStrIreplaceEmptySearch()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_str_ireplace('','x',$str),$str);
    }

    function testMbStrIreplaceEmptySearchInArray()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_str_ireplace(array(''),'x',$str),$str);
    }

    function testMbStrIreplaceEmptyAndValidSearchInArray()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Ixtërnâtiônàlizætiøn';
        $this->assertSame(mb_str_ireplace(array('','ñ'),'x',$str),$replaced);
    }

    function testMbStrIreplaceReplaceArrayAsciiSearch()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñyërxâyiôxàlizæyiøx';
        $this->assertSame(mb_str_ireplace(array('n','t'),array('x','y'),$str),
                            $replaced);
    }

    function testMbStrIreplaceReplaceArrayUtf8Search()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâti??nàliz????ti???n';
        $this->assertSame(mb_str_ireplace(array('Ñ','ô','ø','Æ'),
                                            array('?','??','???','????'),
                                            $str),  $replaced);
    }

    function testMbStrIreplaceReplaceArrayStringReplace()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâti?nàliz?ti?n';
        $this->assertSame(mb_str_ireplace(array('Ñ','ô','ø','Æ'),'?',$str),
                            $replaced);
    }

    function testMbStrIreplaceReplaceArraySingleArrayReplace()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâtinàliztin';
        $this->assertSame(mb_str_ireplace(array('Ñ','ô','ø','Æ'),
                                            array('?'),$str),
                            $replaced);
    }

    function testMbStrIreplaceReplaceLinefeed()
    {
        $str =      "Iñtërnâti\nônàlizætiøn";
        $replaced = "Iñtërnâti\nônàlisetiøn";
        $this->assertSame(mb_str_ireplace('lIzÆ','lise',$str),$replaced);
    }

    function testMbStrIreplaceReplaceLinefeedSearch()
    {
        $str =      "Iñtërnâtiônàli\nzætiøn";
        $replaced = "Iñtërnâtiônàlisetiøn";
        $this->assertSame(mb_str_ireplace("lI\nzÆ",'lise',$str),$replaced);
    }

    function testLtrimDefault()
    {
        $str = '     Iñtërnâtiônàlizætiøn ';
        $trimmed = 'Iñtërnâtiônàlizætiøn ';
        $this->assertSame(mb_ltrim($str),$trimmed);
    }

    function testLtrimCharsetSpecified()
    {
        $str = 'ñtërnâtiônàlizætiøn';
        $trimmed = 'tërnâtiônàlizætiøn';
        $this->assertSame(mb_ltrim($str,'ñ'),$trimmed);
    }

    function testNoLtrim()
    {
        $str = ' Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_ltrim($str,'ñ'),$str);
    }

    function testLtrimEmptyString()
    {
        $str = '';
        $this->assertSame(mb_ltrim($str),$str);
    }

    function testLtrimWithHash()
    {
        $str = '#Iñtërnâtiônàlizætiøn';
        $trimmed = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_ltrim($str,'#'),$trimmed);
    }

    function testLtrimNegateCharClass()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_ltrim($str,'^s'),$str);
    }

    function testLtrimLinefeed()
    {
        $str = "ñ\nñtërnâtiônàlizætiøn";
        $trimmed = "\nñtërnâtiônàlizætiøn";
        $this->assertSame(mb_ltrim($str,'ñ'),$trimmed);
    }

    function testLtrimLinefeedMask()
    {
        $str = "ñ\nñtërnâtiônàlizætiøn";
        $trimmed = "tërnâtiônàlizætiøn";
        $this->assertSame(mb_ltrim($str,"ñ\n"),$trimmed);
    }

    function testRtrimDefault()
    {
        $str = ' Iñtërnâtiônàlizætiøn      ';
        $trimmed = ' Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_rtrim($str),$trimmed);
    }

    function testRtrim()
    {
        $str = 'øIñtërnâtiônàlizætiø';
        $trimmed = 'øIñtërnâtiônàlizæti';
        $this->assertSame(mb_rtrim($str,'ø'),$trimmed);
    }

    function testNoRtrim()
    {
        $str = 'Iñtërnâtiônàlizætiøn ';
        $this->assertSame(mb_rtrim($str,'ø'),$str);
    }

    function testRtrimEmptyString()
    {
        $this->assertSame(mb_rtrim(''),'');
    }

    function testRtrimLinefeed()
    {
        $str = "Iñtërnâtiônàlizætiø\nø";
        $trimmed = "Iñtërnâtiônàlizætiø\n";
        $this->assertSame(mb_rtrim($str,'ø'),$trimmed);
    }

    function testRtrimLinefeedMask()
    {
        $str = "Iñtërnâtiônàlizætiø\nø";
        $trimmed = "Iñtërnâtiônàlizæti";
        $this->assertSame(mb_rtrim($str,"ø\n"),$trimmed);
    }

    function testTrimDefault()
    {
        $str = '  Iñtërnâtiônàlizætiøn   ';
        $trimmed = 'Iñtërnâtiônàlizætiøn';
        $this->assertSame(mb_trim($str),$trimmed);
    }

    function testTrimWithSpecifiedChars()
    {
        $str = 'ñtërnâtiônàlizætiø';
        $trimmed = 'tërnâtiônàlizæti';
        $this->assertSame(mb_trim($str,'ñø'),$trimmed);
    }

    function testNoTrim()
    {
        $str = ' Iñtërnâtiônàlizætiøn ';
        $this->assertSame(mb_trim($str,'ñø'),$str);
    }

    function testTrimEmptyString()
    {
        $this->assertSame(mb_trim(''),'');
    }

    function testStrReplaceCountUnderLimit()
    {
        $str = 'Iñtôërnâtiônàliôzætôiøn';
        $expected = 'Iñttëstërnâtitëstnàlitëstzættëstiøn';
        $replace = str_replace_count('ô','tëst',$str,10);
        $this->assertSame($replace,$expected);
    }

    function testStrReplaceCountAtLimit()
    {
        $str = 'Iñtôërnâtiônàliôzætôiøn';
        $expected = 'Iñttëstërnâtitëstnàlitëstzættëstiøn';
        $replace = str_replace_count('ô','tëst',$str,4);
        $this->assertSame($replace,$expected);
    }

    function testStrReplaceCountOverLimit()
    {
        $str = 'Iñtôërnâtiônàliôzætôiøn';
        $expected = 'Iñttëstërnâtitëstnàliôzætôiøn';
        $replace = str_replace_count('ô','tëst',$str,2);
        $this->assertSame($replace,$expected);
    }

}
