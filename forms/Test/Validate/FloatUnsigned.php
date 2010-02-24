<?php
/**
 * Unit test cases for the T_Validate_FloatUnsigned class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_FloatUnsigned unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_FloatUnsigned extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnValidPositiveFloat()
    {
        $filter = new T_Validate_FloatUnsigned();
        $this->assertSimilarFloat(5.1,$filter->transform(5.1));
        $this->assertSimilarFloat(5,$filter->transform(5));
        $this->assertSimilarFloat(10.1234,$filter->transform(10.1234));
        $this->assertSimilarFloat(0.1234,$filter->transform(0.1234));
        $this->assertSimilarFloat(0,$filter->transform(0));
    }

    function testFilterCastsToFloat()
    {
        $filter = new T_Validate_FloatUnsigned();
        $this->assertSimilarFloat(5.1,$filter->transform('5.1'));
        $this->assertSimilarFloat(5,$filter->transform('5'));
        $this->assertSimilarFloat(5.0,$filter->transform('5.'));
        $this->assertSimilarFloat(0.8,$filter->transform('.8'));
    }

    function testFilterFailsWhenNegativeNumber()
    {
        $filter = new T_Validate_FloatUnsigned();
        $invalid = array(-5,-0.34,'-0.6','-10');
        foreach ($invalid as $term) {
            try {
                _transform($term,$filter);
                $this->fail("Accepted value $term");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testFilterFailsWhenNotANumber()
    {
        $filter = new T_Validate_FloatUnsigned();
        $invalid = array('','@','0.y7','0.8.0','8%','6.7$7','10..5','.');
        foreach ($invalid as $term) {
            try {
                _transform($term,$filter);
                $this->fail("Accepted value $term");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testPipePriorFilter()
    {
        $filter = new T_Validate_FloatUnsigned(new T_Test_Filter_Suffix('.1'));
        $this->assertSimilarFloat($filter->transform('1'),1.1);
    }

}