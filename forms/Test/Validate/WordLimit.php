<?php
/**
 * Unit test cases for the T_Validate_WordLimit class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_WordLimit unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_WordLimit extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnWordLengthWithinRange()
    {
        $filter = new T_Validate_WordLimit(1,5);
        $this->assertSame($filter->transform('The quick fox'),'The quick fox');
    }

    function testFilterIncludesLowerLimit()
    {
        $filter = new T_Validate_WordLimit(3,10);
        $this->assertSame($filter->transform('The quick fox'),'The quick fox');
    }

    function testFilterIncludesUpperLimit()
    {
        $filter = new T_Validate_WordLimit(1,3);
        $this->assertSame($filter->transform('The quick fox'),'The quick fox');
    }

    function testFilterFailsIfNumberOfWordsOutOfRange()
    {
        $filter = new T_Validate_WordLimit(3,5);
        $invalid = array('The','The quick','The quick fox jumped over the');
        foreach ($invalid as $term) {
            try {
                $filter->transform($term);
                $this->fail("Failed on term $term");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testNullLowerLimitRepresentsNoLowerLimit()
    {
        $filter = new T_Validate_WordLimit(null,4);
        $this->assertSame($filter->transform(''),'');
        $this->assertSame($filter->transform('The quick'),'The quick');
        try {
            $filter->transform('The quick brown fox jumped');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testNullUpperLimitRepresentsNoUpperLimit()
    {
        $filter = new T_Validate_WordLimit(2,null);
        $this->assertSame($filter->transform('The quick'),'The quick');
        $this->assertSame($filter->transform('a b c d e f g h i j'),'a b c d e f g h i j');
        try {
            $filter->transform('The');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testNullBothLimitsRepresentsNoLimits()
    {
        $filter = new T_Validate_WordLimit(null,null);
        $this->assertSame($filter->transform(''),'');
        $this->assertSame($filter->transform('a b c d e f g h i j'),'a b c d e f g h i j');
    }

    function testPipePriorFilter()
    {
        $pipe = new T_Test_Filter_Suffix(' fox jumped');
        $filter = new T_Validate_WordLimit(1,20,$pipe);
        $this->assertSame($filter->transform('The quick brown'),$pipe->transform('The quick brown'));
    }

    function testDashesArePartOfSingleWord()
    {
        $filter = new T_Validate_WordLimit(2,2); // must be 2 words
        $this->assertSame($filter->transform($str='the-quick brown'),$str);
        $this->assertSame($filter->transform($str='fox the-quick-brown'),$str);
    }

    function testNumbersAreWordDelimiters()
    {
        $filter = new T_Validate_WordLimit(2,2); // must be 2 words
        $this->assertSame($filter->transform($str='the3quick'),$str);
        $this->assertSame($filter->transform($str='the345quick'),$str);
    }

    function testPunctuationIsNotIncludedInCount()
    {
        $filter = new T_Validate_WordLimit(2,2); // must be 2 words
        $this->assertSame($filter->transform($str='the. brown? ?!?'),$str);
        $this->assertSame($filter->transform($str='fox " & the-quick-brown'),$str);
    }

    function testHandlesUtf8Words()
    {
        $filter = new T_Validate_WordLimit(2,2); // must be 2 words
        $this->assertSame($filter->transform($str='Iñtërnâtiônàlizætiøn Iñtërnâtiônàlizætiøn'),$str);
        $this->assertSame($filter->transform($str='Iñtërnâtiônàlizætiøn " & Iñtër-nâti-ônàliz-ætiøn'),$str);
    }

}
