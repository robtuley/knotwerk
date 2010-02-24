<?php
abstract class T_Test_Form_ScalarElementHarness
         extends T_Test_Form_ElementHarness
{

    function testOptionalElementNotPresentWithZeroLengthStringInput()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()
              ->validate(new T_Cage_Array(array('myalias'=>'')));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),null);
    }

    function testRequiredElementErrorWithZeroLengthStringInput()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->validate(new T_Cage_Array(array('myalias'=>'')));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),false);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
    }

    function testValidElementWithNoValidationFilters()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),'value1');
        if ($input->isRedisplayValid()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testValidElementWithOneFilter()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_Suffix();
        $input->attachFilter($filter)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$filter->transform('value1'));
        if ($input->isRedisplayValid()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testValidElementWithTwoFilters()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $f1 = new T_Test_Filter_Suffix('TestFirst');
        $f2 = new T_Test_Filter_Suffix('TestSecond');
        $input->attachFilter($f1)
              ->attachFilter($f2)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $expect = $f2->transform($f1->transform('value1'));
        $this->assertSame($input->getValue(),$expect);
        if ($input->isRedisplayValid()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testValidateHasFluentInterfaceWithPresentValidElement()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input,$test);
    }

    function testSaltedValidElementSubmissionExtractedOk()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_Suffix();
        $input->attachFilter($filter)
              ->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $source = new T_Cage_Array(array($input->getFieldname()=>'value1'));
        $input->validate($source);
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$filter->transform('value1'));
        if ($input->isRedisplayValid()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testInvalidElementWithOneFilterFail()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testInvalidElementWithFirstFilterFail()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->attachFilter(new T_Test_Filter_Suffix())
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testInvalidElementWithLastFilterFail()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Suffix())
              ->attachFilter(new T_Test_Filter_Failure())
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testSaltedInvalidElementSubmission()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $source = new T_Cage_Array(array($input->getFieldname()=>'value1'));
        $input->validate($source);
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        if ($this->isRedisplayInvalidByDefault()) {
            $this->assertSame($input->getDefault(),'value1');
        } else {
            $this->assertSame($input->getDefault(),null);
        }
    }

    function testValidateHasFluentInterfaceWithPresentInvalidElement()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->attachFilter(new T_Test_Filter_Failure())
                      ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input,$test);
    }

    function testRepeatedValidateClearsPreviousError()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Validate_ArrayMember(array('value1'));
        $input->attachFilter($f);
        /* fail first */
        $input->validate(new T_Cage_Array(array('alias'=>'value2')));
        $this->assertFalse($input->isValid());
        /* now pass */
        $input->validate(new T_Cage_Array(array('alias'=>'value1')));
        $this->assertTrue($input->isValid());
        $this->assertFalse($input->getError());
    }

    function testRepeatedValidateClearsPreviousCleanValue()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Validate_MaxLength(5);
        $input->attachFilter($f);
        /* now pass */
        $input->validate(new T_Cage_Array(array('alias'=>'12345')));
        /* fail first */
        $input->validate(new T_Cage_Array(array('alias'=>'123456')));
        $this->assertFalse($input->isValid());
        $this->assertSame(null,$input->getValue());
    }

    function testCanSerialiseValidatedElement()
    {
        $input = $this->getInputElement('alias','label');
        $input->attachFilter(new T_Test_Filter_Suffix());
        $input->validate(new T_Cage_Array(array('alias'=>'12345')));
        $test = unserialize(serialize($input));
        $this->assertEquals($input,$test);
    }

    function testCanRemoveFilterWithSingleFilterSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_Failure();
        $input->attachFilter($filter)
              ->removeFilter($filter)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertTrue($input->isValid());
    }

    function testCanRemoveFilterWithMultipleFiltersSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $keep = new T_Test_Filter_Suffix();
        $delete = new T_Test_Filter_Failure();
        $input->attachFilter($keep)
              ->attachFilter($delete)
              ->removeFilter($delete)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertTrue($input->isValid());
        $this->assertSame($input->getValue(),$keep->transform('value1'));
    }

    function testElementIsNotSubmittedIfZeroLengthStringPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>''));
        $this->assertFalse($input->isSubmitted($source));
    }

    function testElementIsSubmittedIfNonZeroLengthStringPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>'value1'));
        $this->assertTrue($input->isSubmitted($source));
    }

    function testInvalidElementRedisplayedWhenRedisplayInvalidSetToTrue()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->redisplayInvalid(true)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->getDefault(),'value1');
    }

    function testInvalidElementNotRedisplayedWhenRedisplayInvalidSetToFalse()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->redisplayInvalid(false)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->getDefault(),null);
    }

    function testValidElementRedisplayedWhenRedisplayValidSetToTrue()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->redisplayValid(true)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->getDefault(),'value1');
    }

    function testValidElementNotRedisplayedWhenRedisplayValidSetToFalse()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->redisplayValid(false)
              ->validate(new T_Cage_Array(array('myalias'=>'value1')));
        $this->assertSame($input->getDefault(),null);
    }

}
