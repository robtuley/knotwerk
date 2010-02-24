<?php
abstract class T_Test_Form_ArrayElementHarness
         extends T_Test_Form_ElementHarness
{

    /**
     * Gets an example default.
     *
     * @return string
     */
    protected function getSampleDefault()
    {
        return array('value1');
    }

    function testOptionalElementNotPresentWithEmptyArrayInput()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()
              ->validate(new T_Cage_Array(array('myalias'=>array())));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),null);
    }

    function testRequiredElementErrorWithEmptyArrayInput()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->validate(new T_Cage_Array(array('myalias'=>array())));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),false);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
    }

    function testValidElementWithNoValidationFilters()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),array('value1'));
        $this->assertSame($input->getDefault(),array('value1'));
    }

    function testValidElementWithOneFilter()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_ArrayPrefix();
        $input->attachFilter($filter)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$filter->transform(array('value1')));
        $this->assertSame($input->getDefault(),array('value1'));
    }

    function testValidElementWithTwoFilters()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $f1 = new T_Test_Filter_ArrayPrefix('TestFirst');
        $f2 = new T_Test_Filter_ArrayPrefix('TestSecond');
        $input->attachFilter($f1)
              ->attachFilter($f2)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $expect = $f2->transform($f1->transform(array('value1')));
        $this->assertSame($input->getValue(),$expect);
        $this->assertSame($input->getDefault(),array('value1'));
    }

    function testValidateHasFluentInterfaceWithPresentValidElement()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>array('value1')));
        $test = $input->validate($source);
        $this->assertSame($input,$test);
    }

    function testSaltedValidElementSubmissionExtractedOk()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_ArrayPrefix();
        $input->attachFilter($filter)
              ->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $source = new T_Cage_Array(array($input->getFieldname()=>array('value1')));
        $input->validate($source);
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$filter->transform(array('value1')));
        $this->assertSame($input->getDefault(),array('value1'));
    }

    function testInvalidElementWithOneFilterFail()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),array('value1'));
        } else {
            $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
        }
    }

    function testInvalidElementWithFirstFilterFail()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->attachFilter(new T_Test_Filter_ArrayPrefix())
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),array('value1'));
        } else {
            $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
        }
    }

    function testInvalidElementWithLastFilterFail()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_ArrayPrefix())
              ->attachFilter(new T_Test_Filter_Failure())
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),array('value1'));
        } else {
            $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
        }
    }

    function testSaltedInvalidElementSubmission()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $source = new T_Cage_Array(array($input->getFieldname()=>array('value1')));
        $input->validate($source);
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),array('value1'));
        } else {
            $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
        }
    }

    function testValidateHasFluentInterfaceWithPresentInvalidElement()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->attachFilter(new T_Test_Filter_Failure())
                      ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input,$test);
    }

    function testRepeatedValidateClearsPreviousError()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Validate_ArraySubset(array('value1','value2'));
        $input->attachFilter($f);
        /* fail first */
        $input->validate(new T_Cage_Array(array('alias'=>array('value3'))));
        $this->assertFalse($input->isValid());
        /* now pass */
        $input->validate(new T_Cage_Array(array('alias'=>array('value1'))));
        $this->assertTrue($input->isValid());
        $this->assertFalse($input->getError());
    }

    function testRepeatedValidateClearsPreviousCleanValue()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Validate_ArraySubset(array('value1','value2'));
        $input->attachFilter($f);
        /* pass */
        $input->validate(new T_Cage_Array(array('alias'=>array('value1'))));
        /* now fail */
        $input->validate(new T_Cage_Array(array('alias'=>array('value3'))));
        $this->assertFalse($input->isValid());
        $this->assertSame(null,$input->getValue());
    }

    function testCanSerialiseValidatedElement()
    {
        $input = $this->getInputElement('alias','label');
        $input->attachFilter(new T_Test_Filter_ArrayPrefix());
        $input->validate(new T_Cage_Array(array('alias'=>array('value1'))));
        $test = unserialize(serialize($input));
        $this->assertEquals($input,$test);
    }

    function testCanRemoveFilterWithSingleFilterSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_Failure();
        $input->attachFilter($filter)
              ->removeFilter($filter)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertTrue($input->isValid());
    }

    function testCanRemoveFilterWithMultipleFiltersSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $keep = new T_Test_Filter_ArrayPrefix();
        $delete = new T_Test_Filter_Failure();
        $input->attachFilter($keep)
              ->attachFilter($delete)
              ->removeFilter($delete)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertTrue($input->isValid());
        $this->assertSame($input->getValue(),$keep->transform(array('value1')));
    }

    function testElementIsNotSubmittedIfEmptyArrayPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>array()));
        $this->assertFalse($input->isSubmitted($source));
    }

    function testElementIsSubmittedIfNonEmptyArrayPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>array('value1')));
        $this->assertTrue($input->isSubmitted($source));
    }

    function testInvalidElementRedisplayedWhenRedisplayInvalidIsTrue()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->redisplayInvalid(true)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->getDefault(),array('value1'));
    }

    function testInvalidElementNotRedisplayedWhenRedisplayInvalidIsFalse()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->redisplayInvalid(false)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
    }

    function testValidElementRedisplayedWhenRedisplayValidisTrue()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->redisplayValid(true)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->getDefault(),array('value1'));
    }

    function testValidElementNotRedisplayedWhenRedisplayValidisFalse()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->redisplayValid(false)
              ->validate(new T_Cage_Array(array('myalias'=>array('value1'))));
        $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
    }

}
