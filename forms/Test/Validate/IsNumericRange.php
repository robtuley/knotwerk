<?php
/**
 * Unit test cases for the T_Validate_IsNumericRange class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_IsNumericRange unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_IsNumericRange extends T_Test_Filter_SkeletonHarness
{

    function testFilterFailsIfMinFieldIsNotAvailable()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $test->addChild(new T_Test_Form_ElementStub('max','label'));
        try {
            $filter->transform($test);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFilterFailsIfMaxFieldIsNotAvailable()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $test->addChild(new T_Test_Form_ElementStub('min','label'));
        try {
            $filter->transform($test);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFilterDoesNoActionIfMinAlreadyHasError()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $min = new T_Test_Form_ElementStub('min','label');
        $min->attachFilter(new T_Test_Filter_Failure());
        $test->addChild($min);
        $max = new T_Test_Form_ElementStub('max','label');
        $test->addChild($max);
        $test->validate(new T_Cage_Array(array('min'=>1.1,'max'=>0.9)));
        $filter->transform($test);
        $this->assertTrue($max->isValid());
        $this->assertFalse($min->isValid());
    }

    function testFilterDoesNoActionIfMaxAlreadyHasError()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $min = new T_Test_Form_ElementStub('min','label');
        $test->addChild($min);
        $max = new T_Test_Form_ElementStub('max','label');
        $max->attachFilter(new T_Test_Filter_Failure());
        $test->addChild($max);
        $test->validate(new T_Cage_Array(array('min'=>1.1,'max'=>0.9)));
        $filter->transform($test);
        $this->assertFalse($max->isValid());
        $this->assertTrue($min->isValid());
    }

    function testFilterDoesNoActionIfMinAndMaxNotPresentButValid()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $min = new T_Test_Form_ElementStub('min','label');
        $min->setOptional();
        $test->addChild($min);
        $max = new T_Test_Form_ElementStub('max','label');
        $max->setOptional();
        $test->addChild($max);
        $test->validate(new T_Cage_Array(array()));
        $filter->transform($test);
        $this->assertTrue($test->isValid());
    }

    function testFilterDoesNoActionIfMinSmallerThanMax()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $min = new T_Test_Form_ElementStub('min','label');
        $test->addChild($min);
        $max = new T_Test_Form_ElementStub('max','label');
        $test->addChild($max);
        $test->validate(new T_Cage_Array(array('min'=>0.9,'max'=>1.2)));
        $filter->transform($test);
        $this->assertTrue($test->isValid());
    }

    function testFilterAddsErrorToMaxIfSmallerThanMin()
    {
        $filter = new T_Validate_IsNumericRange('min','max');
        $test = new T_Form_Fieldset('container','label');
        $min = new T_Test_Form_ElementStub('min','label');
        $test->addChild($min);
        $max = new T_Test_Form_ElementStub('max','label');
        $test->addChild($max);
        $test->validate(new T_Cage_Array(array('min'=>1.2,'max'=>0.9)));
        $filter->transform($test);
        $this->assertFalse($max->isValid());
        $this->assertTrue($min->isValid());
        $this->assertTrue($max->getError() instanceof T_Form_Error);
    }

    function testPipePriorFilter()
    {
        $filter = new T_Validate_IsNumericRange('min','max',new T_Test_Filter_Failure());
        $test = new T_Form_Fieldset('container','label');
        $min = new T_Test_Form_ElementStub('min','label');
        $test->addChild($min);
        $max = new T_Test_Form_ElementStub('max','label');
        $test->addChild($max);
        $test->validate(new T_Cage_Array(array('min'=>0.9,'max'=>1.2)));
        try {
            $filter->transform($test);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

}