<?php
abstract class T_Test_Form_ElementHarness extends T_Unit_Case
{

    abstract protected function getInputElement($alias,$label);

    protected function getNewInstanceDefault()
    {
        return null;
    }

    protected function getSampleDefault()
    {
        return 'value1';  /* is part of default options array */
    }

    function isRedisplayInvalidByDefault()
    {
        return true;
    }

    // tests

    function testAliasSetThroughConstructor()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($input->getAlias(),'myalias');
    }

    function testAliasCanBeChanged()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($input,$input->setAlias('new'));
        $this->assertSame($input->getAlias(),'new');
    }

    function testLabelSetThroughConstructor()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($input->getLabel(),'mylabel');
    }

    function testFilterCanBePassedToGetLabelMethod()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $f =  new T_Test_Filter_Suffix();
        $this->assertSame($input->getLabel($f),$f->transform('mylabel'));
    }

    function testAliasCastToStringInConstructor()
    {
        $input = $this->getInputElement(123,'mylabel');
        $this->assertSame($input->getAlias(),'123');
    }

    function testLabelCastToStringInConstructor()
    {
        $input = $this->getInputElement('myalias',123);
        $this->assertSame($input->getLabel(),'123');
    }

    function testConstructorFailureWithZeroLengthAlias()
    {
        try {
            $input = $this->getInputElement(null,'mylabel');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testConstructorFailureWithZeroLengthLabel()
    {
        try {
            $input = $this->getInputElement('myalias',null);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFieldnameIsAliasByDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame('myalias',$input->getFieldname());
    }

    function testSaltedFieldnameIsHashed()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $name = $input->getFieldname();
        $this->assertNotEquals('myalias',$name);
        $this->assertTrue(ctype_xdigit($name),"$name is not xdigit");
    }

    function testSaltedFieldnameFirstCharIsLetter()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $field = $input->getFieldname();
        $this->assertTrue(ctype_alpha($field[0]));
    }

    function testRepeatedCallsToSaltedGetFieldnameProduceSameResults()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($input->getFieldname(),$input->getFieldname());
    }

    function testDifferentSaltProducesDifferentFieldname()
    {
        $input1 = $this->getInputElement('myalias','mylabel');
        $input2 = $this->getInputElement('myalias','mylabel');
        $input1->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $input2->setFieldnameSalt('salt2',new T_Filter_RepeatableHash());
        $this->assertNotSame($input1->getFieldname(),$input2->getFieldname());
    }

    function testSetFieldnameSaltHasFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($test,$input);
    }

    function testSaltCanBeChanged()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $field1 = $input->getFieldname();
        $input->setFieldnameSalt('change',new T_Filter_RepeatableHash());
        $field2 = $input->getFieldname();
        $this->assertNotEquals($field1,$field2);
    }

    function testNoDefaultInNewInstance()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($this->getNewInstanceDefault(),$input->getDefault());
    }

    function testDefaultSetAndGetWhenRedisplayValidOnly()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setDefault($this->getSampleDefault())
              ->redisplayValid(true)
              ->redisplayInvalid(false);
        $this->assertSame($this->getSampleDefault(),$input->getDefault());
    }

    function testDefaultSetAndGetWhenRedisplayValidAndRedisplayInvalid()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setDefault($this->getSampleDefault())
              ->redisplayValid(true)
              ->redisplayInvalid(true);
        $this->assertSame($this->getSampleDefault(),$input->getDefault());
    }

    function testDefaultSetAndGetWhenRedisplayInvalidOnlyAndIsValid()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setDefault($this->getSampleDefault())
              ->redisplayValid(false)
              ->redisplayInvalid(true);
        $this->assertSame($this->getNewInstanceDefault(),$input->getDefault());
    }

    function testDefaultSetAndGetWhenRedisplayInvalidOnlyAndIsInvalid()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setDefault($this->getSampleDefault())
              ->setError(new T_Form_Error('test'))
              ->redisplayValid(false)
              ->redisplayInvalid(true);
        $this->assertSame($this->getSampleDefault(),$input->getDefault());
    }

    function testDefaultIsNotAffectedWhenRedisplayNone()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setDefault($this->getSampleDefault())
              ->redisplayValid(false)
              ->redisplayInvalid(false);
        $this->assertSame($this->getNewInstanceDefault(),$input->getDefault());
    }

    function testDefaultCanBeFilteredDuringRetrieval()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setDefault($this->getSampleDefault())
              ->redisplayValid(true);
        if (is_array($this->getSampleDefault())) {
            $f = new T_Test_Filter_ArrayPrefix();
        } else {
            $f = new T_Test_Filter_Suffix();
        }
        $this->assertSame($f->transform($this->getSampleDefault()),$input->getDefault($f));
    }

    function testSetDefaultHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setDefault($this->getSampleDefault());
        $this->assertSame($test,$input);
    }

    function testGetValueFunctionReturnsNullByDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame(null,$input->getValue());
    }

    function testElementIsValidAndNotPresentByDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
    }

    function testSetOptionalMethodHasFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setOptional();
        $this->assertSame($test,$input);
    }

    function testAttachFilterMethodHasFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->attachFilter(new T_Test_Filter_Failure());
        $this->assertSame($test,$input);
    }

    function testOptionalElementWithInputNotSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()->validate(new T_Cage_Array(array()));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),null);
    }

    function testRequiredElementWithInputNotSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->validate(new T_Cage_Array(array()));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),false);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
    }

    function testValidationFiltersNotAppliedWhenNotPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()
              ->attachFilter(new T_Test_Filter_Failure())
              ->validate(new T_Cage_Array(array()));
        $this->assertSame($input->isValid(),true);
    }

    function testDefaultValueIsClearedWithInputNotSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()
              ->setDefault($this->getSampleDefault())
              ->validate(new T_Cage_Array(array()));
        $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
    }

    function testSaltedFieldnameIsTestedForPresenceOfInput()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()
              ->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash())
              ->validate(new T_Cage_Array(array('myalias'=>'input')));
        $this->assertSame($input->isPresent(),false);
    }

    function testValidateMethodHasFluentInterfaceWithInputNotSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setOptional()->validate(new T_Cage_Array(array()));
        $this->assertSame($input,$test);
    }

    function testElementCanBeSerialized()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = unserialize(serialize($input));
        $this->assertEquals($input,$test);
    }

    function testElementImplementsCompositeLeaf()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertTrue($input instanceof T_CompositeLeaf);
    }

    function testElementIsVistatorable()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertTrue($input instanceof T_Visitorable);
    }

    function testThatElementIsNotAComposite()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertTrue(is_null($input->getComposite()));
    }

    function testThatTheElementCanAcceptAVisitor()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $visitor = new T_Test_VisitorStub();
        $input->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$input);
        $this->assertTrue(count($visited['object'])==1);
    }

    function testSearchForElementReturnsObjectIfMatchesAlias()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame($input,$input->search('myalias'));
    }

    function testSearchForElementReturnsObjIfMatchesAliasEvenWhenSalted()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($input,$input->search('myalias'));
    }

    function testSearchForElementFalseIfAliasDoesNotMatch()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertFalse($input->search('diffalias'));
    }

    function testSearchForElementIsCaseSenstiveForAliasCheck()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertFalse($input->search('mYalias'));
    }

    function testInputElementIsRequiredByDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertTrue($input->isRequired());
    }

    function testSetOptionalMethodResultsInElementNotBeingRequired()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional();
        $this->assertFalse($input->isRequired());
    }

    function testSetRequiredMethodResultsInElementBeingRequired()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()->setRequired();
        $this->assertTrue($input->isRequired());
    }

    function testSetRequiredMethodHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setRequired();
        $this->assertSame($input,$test);
    }

    function testRemoveFilterMethodHasFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $filter = new T_Test_Filter_Failure();
        $input->attachFilter($filter);
        $test = $input->removeFilter($filter);
        $this->assertSame($test,$input);
    }

    function testRemoveFilterMethodFailsIfNoMatchingFilterFound()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure());
        try {
            $input->removeFilter(new T_Test_Filter_Failure());
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testElementIsNotSubmittedIfNotPresent()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertFalse($input->isSubmitted(new T_Cage_Array(array())));
    }

    function testRedisplayInvalidMethodHasFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->redisplayInvalid(true);
        $this->assertSame($test,$input);
    }

    function testRedisplayValidMethodHasFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->redisplayValid(true);
        $this->assertSame($test,$input);
    }

    function testRedisplayValidStatusCanBeQueried()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->redisplayValid(true);
        $this->assertTrue($input->isRedisplayValid());
        $input->redisplayValid(false);
        $this->assertFalse($input->isRedisplayValid());
    }

    function testCanChangeLabel()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setLabel('alt');
        $this->assertSame('alt',$input->getLabel());
    }

    function testSetLabelHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setLabel('alt');
        $this->assertSame($test,$input);
    }

    function testExplicitallyCanSetError()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $error = new T_Form_Error('example');
        $input->setError($error);
        $this->assertSame($error,$input->getError());
        $this->assertFalse($input->isValid());
    }

    function testSetErrorHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setError(new T_Form_Error('example'));
        $this->assertSame($test,$input);
    }

    function testCanClearError()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $error = new T_Form_Error('example');
        $input->setError($error);
        $input->clearError();
        $this->assertFalse($input->getError());
        $this->assertTrue($input->isValid());
    }

    function testHelpNoteIsNullByDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $this->assertSame(null,$input->getHelp());
    }

    function testHelpNoteCanBeChanged()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setHelp('help');
        $this->assertSame('help',$input->getHelp());
    }

    function testSetHelpHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setHelp('help');
        $this->assertSame($test,$input);
    }

    // ATTRIBUTES

    function testSetAttributeAddsAnAttribute()
    {
        $input = $this->getInputElement('alias','label');
        $this->assertSame($input->setAttribute('name','value'),$input,'fluent');
        $this->assertSame($input->getAttribute('name'),'value');
        $this->assertSame($input->getAllAttributes(),array('name'=>'value'));
    }

    function testSetAttributeCanBeUsedToAddMultipleAttributes()
    {
        $input = $this->getInputElement('alias','label');
        $input->setAttribute('name1','value1')
              ->setAttribute('name2','value2');
        $this->assertSame($input->getAttribute('name1'),'value1');
        $this->assertSame($input->getAttribute('name2'),'value2');
        $this->assertSame($input->getAllAttributes(),array('name1'=>'value1',
                                                           'name2'=>'value2')  );
    }

    function testSetAttributeCanOverwriteExistingAttributes()
    {
        $input = $this->getInputElement('alias','label');
        $input->setAttribute('name','value1')
              ->setAttribute('name','value2');
        $this->assertSame($input->getAttribute('name'),'value2');
        $this->assertSame($input->getAllAttributes(),array('name'=>'value2'));
    }

    function testGetAllAttributesReturnsEmptyArrayByDefault()
    {
        $input = $this->getInputElement('alias','label');
        $this->assertSame($input->getAllAttributes(),array());
    }

    function testGetAttributeReturnsNullIfNameNotSet()
    {
        $input = $this->getInputElement('alias','label');
        $this->assertSame($input->getAttribute('name'),null);
    }

}
