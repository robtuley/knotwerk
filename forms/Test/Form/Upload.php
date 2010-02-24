<?php
class T_Test_Form_Upload extends T_Unit_Case
{

    function testAliasSetThroughConstructor()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame($input->getAlias(),'myalias');
    }

    function testLabelSetThroughConstructor()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame($input->getLabel(),'mylabel');
    }

    function testFilterCanBePassedToGetLabelMethod()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $f =  new T_Test_Filter_Suffix();
        $this->assertSame($input->getLabel($f),$f->transform('mylabel'));
    }

    function testAliasCastToStringInConstructor()
    {
        $input = new T_Form_Upload(123,'mylabel');
        $this->assertSame($input->getAlias(),'123');
    }

    function testLabelCastToStringInConstructor()
    {
        $input = new T_Form_Upload('myalias',123);
        $this->assertSame($input->getLabel(),'123');
    }

    function testConstructorFailureWithZeroLengthAlias()
    {
        try {
            $input = new T_Form_Upload(null,'mylabel');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testConstructorFailureWithZeroLengthLabel()
    {
        try {
            $input = new T_Form_Upload('myalias',null);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFieldnameIsAliasByDefault()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame('myalias',$input->getFieldname());
    }

    function testSaltedFieldnameIsHashed()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',
                                 new T_Filter_RepeatableHash());
        $this->assertNotEquals('myalias',$input->getFieldname());
        $this->assertTrue(ctype_xdigit($input->getFieldname()));
    }

    function testSaltedFieldnameFirstCharIsLetter()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',
                                 new T_Filter_RepeatableHash());
        $field = $input->getFieldname();
        $this->assertTrue(ctype_alpha($field[0]));
    }

    function testRepeatedCallsToSaltedGetFieldnameProduceSameResults()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',
                                 new T_Filter_RepeatableHash());
        $this->assertSame($input->getFieldname(),$input->getFieldname());
    }

    function testDifferentSaltProducesDifferentFieldname()
    {
        $input1 = new T_Form_Upload('myalias','mylabel');
        $input2 = new T_Form_Upload('myalias','mylabel');
        $input1->setFieldnameSalt('salt1',
                                  new T_Filter_RepeatableHash());
        $input2->setFieldnameSalt('salt2',
                                  new T_Filter_RepeatableHash());
        $this->assertNotSame($input1->getFieldname(),$input2->getFieldname());
    }

    function testSetFieldnameSaltHasFluentInterface()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = $input->setFieldnameSalt('mysalt',
                                         new T_Filter_RepeatableHash());
        $this->assertSame($test,$input);
    }

    function testSaltIsImmutableOnceSet()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('salt1',
                                 new T_Filter_RepeatableHash());
        $field1 = $input->getFieldname();
        $input->setFieldnameSalt('change',
                                 new T_Filter_RepeatableHash());
        $field2 = $input->getFieldname();
        $this->assertNotEquals($field1,$field2);
    }

    function testNoDefaultFieldValueInNewInstance()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame(null,$input->getDefault());
    }

    function testTryingToSetADefaultResultsInError()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        try {
            $input->setDefault('mydefault');
            $this->fail();
        } catch (BadFunctionCallException $e) { }
    }

    function testGetValueFunctionReturnsNullByDefault()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame(null,$input->getValue());
    }

    function testElementIsValidAndNotPresentByDefault()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
    }

    function testSetOptionalMethodHasFluentInterface()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = $input->setOptional();
        $this->assertSame($test,$input);
    }

    function testAttachFilterMethodHasFluentInterface()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = $input->attachFilter(new T_Test_Filter_Failure());
        $this->assertSame($test,$input);
    }

    function testElementWithInputNotAPostArray()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setOptional()->validate(new T_Cage_Array(array()));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),null);
    }

    function testOptionalElementWithInputNotSet()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setOptional()->validate(new T_Cage_Post(array(),array()));
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),false);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),null);
    }

    function testRequiredElementWithInputNotSet()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->validate(new T_Cage_Post(array(),array()));
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),false);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
    }

    function testValidationFiltersNotAppliedWhenNotPresent()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setOptional()
              ->attachFilter(new T_Test_Filter_Failure())
              ->validate(new T_Cage_Post(array(),array()));
        $this->assertSame($input->isValid(),true);
    }

    function testSaltedFieldnameIsTestedForPresenceOfInput()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $files = array('myalias' => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->setOptional()
              ->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash())
              ->validate($src);
        $this->assertSame($input->isPresent(),false);
    }

    function testValidateMethodHasFluentInterfaceWithInputNotSet()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = $input->setOptional()->validate(new T_Cage_Post(array(),array()));
        $this->assertSame($input,$test);
    }

    function testElementCanBeSerialized()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = unserialize(serialize($input));
        $this->assertEquals($input,$test);
    }

    function testElementImplementsCompositeLeaf()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertTrue($input instanceof T_CompositeLeaf);
    }

    function testElementIsVistatorable()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertTrue($input instanceof T_Visitorable);
    }

    function testThatElementIsNotAComposite()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertTrue(is_null($input->getComposite()));
    }

    function testThatTheElementCanAcceptAVisitor()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $visitor = new T_Test_VisitorStub();
        $input->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$input);
        $this->assertTrue(count($visited['object'])==1);
    }

    function testSearchForElementReturnsObjectIfMatchesAlias()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame($input,$input->search('myalias'));
    }

    function testSearchForElementReturnsObjIfMatchesAliasEvenWhenSalted()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($input,$input->search('myalias'));
    }

    function testSearchForElementFalseIfAliasDoesNotMatch()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertFalse($input->search('diffalias'));
    }

    function testSearchForElementIsCaseSensitiveForAliasCheck()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertFalse($input->search('mYalias'));
    }

    function testInputElementIsRequiredByDefault()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertTrue($input->isRequired());
    }

    function testSetOptionalMethodResultsInElementNotBeingRequired()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setOptional();
        $this->assertFalse($input->isRequired());
    }

    function testRemoveFilterMethodHasFluentInterface()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $filter = new T_Test_Filter_Failure();
        $input->attachFilter($filter);
        $test = $input->removeFilter($filter);
        $this->assertSame($test,$input);
    }

    function testRemoveFilterMethodFailsIfNoMatchingFilterFound()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->attachFilter(new T_Test_Filter_Failure());
        try {
            $input->removeFilter(new T_Test_Filter_Failure());
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testElementIsNotSubmittedIfNotPresent()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertFalse($input->isSubmitted(new T_Cage_Array(array())));
        $this->assertFalse($input->isSubmitted(new T_Cage_Post(array(),array())));
    }

    function testRedisplayInvalidMethodHasFluentInterface()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = $input->redisplayInvalid();
        $this->assertSame($test,$input);
    }

    function testValidElementWithNoValidationFilters()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $files = array('myalias' => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$src->asFile('myalias'));
        $this->assertSame($input->getDefault(),null);
    }

    function testValidateHasFluentInterfaceWithPresentValidElement()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $files = array('myalias' => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $test = $input->validate($src);
        $this->assertSame($input,$test);
    }

    function testSaltedValidElementSubmissionExtractedOk()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $files = array($input->getFieldname() => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$src->asFile($input->getFieldname()));
        $this->assertSame($input->getDefault(),null);
    }

    function testElementInvalidDueToFilterError()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $files = array($input->getFieldname() => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->attachFilter(new T_Test_Filter_Failure())
              ->validate($src);
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        $this->assertSame($input->getDefault(),null);
    }

    function testElementInvalidDueToUploadError()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $files = array($input->getFieldname() => new T_Exception_UploadedFile('msg'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $this->assertSame($input->isValid(),false);
        $this->assertSame($input->isPresent(),true);
        $this->assertTrue($input->getError() instanceof T_Form_Error);
        $this->assertSame($input->getValue(),null);
        $this->assertSame($input->getDefault(),null);
    }

    function testRepeatedValidateClearsPreviousError()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $files = array($input->getFieldname() => new T_Exception_UploadedFile('msg'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $files = array($input->getFieldname() => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$src->asFile('myalias'));
        $this->assertSame($input->getDefault(),null);
    }

    function testRepeatedValidateClearsPreviousCleanValue()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $files = array($input->getFieldname() => new T_File_Uploaded('first/path',150,'first.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $files = array($input->getFieldname() => new T_File_Uploaded('some/path',100,'upload.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $this->assertSame($input->isValid(),true);
        $this->assertSame($input->isPresent(),true);
        $this->assertSame($input->getError(),false);
        $this->assertSame($input->getValue(),$src->asFile('myalias'));
        $this->assertSame($input->getDefault(),null);
    }

    function testCanSerialiseValidatedElement()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $files = array($input->getFieldname() => new T_File_Uploaded('first/path',150,'first.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $input->validate($src);
        $test = unserialize(serialize($input));
        $this->assertEquals($input,$test);
    }

    function testCanRemoveFilterWithSingleFilterSet()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $files = array($input->getFieldname() => new T_File_Uploaded('first/path',150,'first.txt'));
        $src = new T_Test_Cage_PostStub(array(),$files);
        $filter = new T_Test_Filter_Failure();
        $input->attachFilter($filter)
              ->removeFilter($filter)
              ->validate($src);
        $this->assertTrue($input->isValid());
    }

    function testMaxSizeNullByDefault()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $this->assertSame($input->getMaxSize(),null);
    }

    function testSetMaxSizeMethodHasAFluentInterface()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $test = $input->setMaxSize('53');
        $this->assertSame($test,$input);
    }

    function testCanSetMaxSize()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setMaxSize(1024*1024);
        $this->assertSame($input->getMaxSize(),1024*1024);
    }

    function testSetMaxSizeMethodCastsInputToInt()
    {
        $input = new T_Form_Upload('myalias','mylabel');
        $input->setMaxSize('53');
        $this->assertSame($input->getMaxSize(),53);
    }

}
