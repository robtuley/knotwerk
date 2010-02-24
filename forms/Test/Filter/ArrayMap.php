<?php
/**
 * Unit test cases for the T_Filter_ArrayMap class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id $
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_ArrayMap test cases.
 *
 * @package formTests
 */
class T_Test_Filter_ArrayMap extends T_Test_Filter_SkeletonHarness
{

    function testFilterAppliesFilterToSingleElementArray()
    {
        $map = new T_Test_Filter_Suffix('end');
        $filter = new T_Filter_ArrayMap($map);
        $this->assertSame($filter->transform(array('value')),array('valueend'));
    }

    function testFilterAppliesFilterToMultiElementArray()
    {
        $map = new T_Test_Filter_Suffix('end');
        $filter = new T_Filter_ArrayMap($map);
        $this->assertSame($filter->transform(array('a','b')),array('aend','bend'));
    }

    function testEmptyArrayNotAffected()
    {
        $map = new T_Test_Filter_Suffix();
        $filter = new T_Filter_ArrayMap($map);
        $this->assertSame($filter->transform(array()),array());
    }

    function testErrorWhenFilterTargetIsNotAnArray()
    {
        $map = new T_Test_Filter_Suffix();
        $filter = new T_Filter_ArrayMap($map);
        try {
            $filter->transform('value');
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testFilterDoesNotRecurse()
    {
        $map = new T_Test_Filter_ArrayPrefix();
        $filter = new T_Filter_ArrayMap($map);
        $target = array( array('value') );
        $expect = array( $map->transform(array('value')) );
        $this->assertSame($filter->transform($target),$expect);
    }

    function testCanPipePriorFilter()
    {
        $map = new T_Test_Filter_Suffix();
        $prior = new T_Test_Filter_ArrayPrefix();
        $filter = new T_Filter_ArrayMap($map,$prior);
        $target = array( 'value' );
        $expect = $prior->transform(array('value'));
        foreach ($expect as &$element) {
            $element = $map->transform($element);
        }
        $this->assertSame($filter->transform($target),$expect);
    }

    function testExceptionGetsReThrown()
    {
        $map = new T_Test_Filter_Failure();
        $filter = new T_Filter_ArrayMap($map);
        try {
            $filter->transform(array('value'));
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

    function testErrorMessageContainsReferenceToFailurePosition()
    {
        $map = new T_Validate_Email();
        $filter = new T_Filter_ArrayMap($map);
        $input = array('fred@example.com','notanemail','joe@example.com');
        try {
            $filter->transform($input);
        } catch (T_Exception_Filter $e) {
            $this->assertContains('2nd',$e->getMessage());
        }
    }

}
