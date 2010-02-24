<?php
/**
 * Unit test cases for the T_Validate_Letters class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Letters test cases.
 *
 * @package formTests
 */
class T_Test_Validate_Letters extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectIfStringAllAsciiLetters()
    {
        $filter = new T_Validate_Letters();
        $this->assertSame($filter->transform('abcDefghiJklmnopqrstuvwxyz'),
                          'abcDefghiJklmnopqrstuvwxyz' );
    }

    function testFilterHasNoEffectIfStringI18nLetters()
    {
        $filter = new T_Validate_Letters();
        $this->assertSame($filter->transform('Iñtërnâtiônàlizætiøn'),
                          'Iñtërnâtiônàlizætiøn' );
    }

    function testFailsOnNonLetterCharacter()
    {
        $filter = new T_Validate_Letters();
        $invalid = array('let%ters','ab!cd','ab"cd','!abcd','abcd!',
                         'ab£','$ab','^ab','a*b','(ab','ab)','a-b',
                         'a_b','a+b','a=','a@b','a:','a;','a>','a?b',
                         'a/b','a~b','a#','a.b','a,b','a¬b','a b',' ','' );
        foreach ($invalid as $value) {
            try {
                $filter->transform($value);
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix('pipe');
        $filter = new T_Validate_Letters($pipe);
        $this->assertSame($filter->transform('first'),'firstpipe');
    }

}
