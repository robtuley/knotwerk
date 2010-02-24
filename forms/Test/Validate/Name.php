<?php
/**
 * Unit test cases for the T_Validate_Name class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Name test cases.
 *
 * @package formTests
 */
class T_Test_Validate_Name extends T_Test_Filter_SkeletonHarness
{

    function testHasNoEffectIfStringAllAsciiLetters()
    {
        $filter = new T_Validate_Name();
        $this->assertSame($filter->transform('abcDefghiJklmnopqrstuvwxyz'),
                          'abcDefghiJklmnopqrstuvwxyz' );
    }

    function testAllowsDashesSpacesAndApostropheInCentreOfString()
    {
        $filter = new T_Validate_Name();
        $valid = array('O\'Connor','Smith-Henderson','van der Merwe',
                       'a\'b','a-b','a b');
        foreach ($valid as $value) {
            $this->assertSame($filter->transform($value),$value);
        }
    }

    function testHasNoEffectIfStringI18nLetters()
    {
        $filter = new T_Validate_Name();
        $this->assertSame($filter->transform('Iñtërnâtiônàlizætiøn'),
                          'Iñtërnâtiônàlizætiøn' );
    }

    function testAcceptsI18nLettersWithDashesSpacesAndApostrophe()
    {
        $filter = new T_Validate_Name();
        $this->assertSame($filter->transform('Iñt\'ërnâ tiôn àliz-ætiøn'),
                          'Iñt\'ërnâ tiôn àliz-ætiøn' );
    }

    function testFailsOnNonLetterCharacter()
    {
        $filter = new T_Validate_Name();
        $invalid = array('let%ters','ab!cd','ab"cd','!abcd','abcd!',
                         'ab£','$ab','^ab','a*b','(ab','ab)',
                         'a_b','a+b','a=','a@b','a:','a;','a>','a?b',
                         'a/b','a~b','a#','a.b','a,b','a¬b','' );
        foreach ($invalid as $value) {
            try {
                $filter->transform($value);
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testFailsWhenDashesSpacesAndApostropheAtStartOrEnd()
    {
        $filter = new T_Validate_Name();
        $invalid = array(' ab','ab ','-ab','ab-','\'ab','ab\'');
        foreach ($invalid as $value) {
            try {
                $filter->transform($value);
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix('pipe');
        $filter = new T_Validate_Name($pipe);
        $this->assertSame($filter->transform('first'),'firstpipe');
    }

}
