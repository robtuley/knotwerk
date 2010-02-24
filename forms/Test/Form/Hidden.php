<?php
class T_Test_Form_Hidden extends T_Unit_Case
{

    function testAliasIsSetInConstruct()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame('alias',$hidden->getAlias());
    }

    function testAliasCanBeChanged()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame($hidden,$hidden->setAlias('new'));
        $this->assertSame($hidden->getAlias(),'new');
    }

    function testConstructFailsWithZeroLengthAlias()
    {
        try {
            $hidden = new T_Form_Hidden('','value');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testValueIsSetInConstruct()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame('value',$hidden->getValue());
    }

    function testValueCanBeChanged()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->setValue('diff');
        $this->assertSame('diff',$hidden->getValue());
    }

    function testSetValueMethodHasAFluentInterface()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame($hidden,$hidden->setValue('diff'));
    }

    function testFieldValueIsValueWithoutAnyFilters()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame('value',$hidden->getFieldValue());
    }

    function testFieldValueIsForwardFilteredWithSingleFilter()
    {
        $f = new T_Test_Filter_Suffix();
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->attachFilter($f);
        $this->assertSame($f->transform('value'),$hidden->getFieldValue());
    }

    function testFieldValueIsForwardFilteredWithMultipleFilter()
    {
        $f = array(new T_Test_Filter_Suffix('1st'),new T_Test_Filter_Suffix('2nd'));
        $expect = 'value';
        foreach ($f as $filter) {
            $expect = $filter->transform($expect);
        }
        $hidden = new T_Form_Hidden('alias','value');
        foreach ($f as $filter) {
            $hidden->attachFilter($filter);
        }
        $this->assertSame($expect,$hidden->getFieldValue());
    }

    function testFieldValueCanBeFilteredOnOutput()
    {
        $f = new T_Test_Filter_Suffix();
        $out = new T_Test_Filter_Suffix('output');
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->attachFilter($f);
        $this->assertSame($out->transform($f->transform('value')),
                          $hidden->getFieldValue($out));
    }

    function testSearchReturnsFalseIfNotAlias()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertFalse($hidden->search('diff'));
    }

    function testSearchOnAliasIsCaseSensitive()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertFalse($hidden->search('ALIAS'));
    }

    function testSearchReturnsSelfIfAliasMatches()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame($hidden,$hidden->search('alias'));
    }

    function testAttachFilterMethodHasAFluentInterface()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame($hidden,$hidden->attachFilter(new T_Test_Filter_Suffix()));
    }

    function testGetCompositeReturnsNull()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertTrue(is_null($hidden->getComposite()));
    }

    function testIsSubmittedIsFalseIfNotPresent()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $cage = new T_Cage_Array(array());
        $this->assertFalse($hidden->isSubmitted($cage));
    }

    function testIsSubmittedIsTrueIfMainFieldnamePresent()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $cage = new T_Cage_Array(array($hidden->getFieldname()=>'value'));
        $this->assertTrue($hidden->isSubmitted($cage));
    }

    function testValidatesWithNoErrorIfValueAndChecksumArePresent()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $src = array($hidden->getFieldname() => $hidden->getFieldValue(),
                     $hidden->getChecksumFieldname() => $hidden->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertTrue($hidden->isValid());
        $this->assertTrue($hidden->isPresent());
        $this->assertFalse($hidden->getError());
        $this->assertSame('value',$hidden->getValue());
    }

    function testValidatesWithNoErrorIfValueAndChecksumArePresentAndSaltSet()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
        $src = array($hidden->getFieldname() => $hidden->getFieldValue(),
                     $hidden->getChecksumFieldname() => $hidden->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertTrue($hidden->isValid());
        $this->assertTrue($hidden->isPresent());
        $this->assertFalse($hidden->getError());
        $this->assertSame('value',$hidden->getValue());
    }

    function testValidateWithErrorIfChecksumMissing()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $src = array($hidden->getFieldname() => $hidden->getFieldValue() );
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertFalse($hidden->isValid());
        $this->assertTrue($hidden->isPresent());
        $this->assertTrue(($hidden->getError() instanceof T_Form_Error ));
        $this->assertSame('value',$hidden->getValue());
    }

    function testValidateWithErrorIfChecksumIsIncorrect()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $src = array($hidden->getFieldname() => $hidden->getFieldValue(),
                     $hidden->getChecksumFieldname() => 'notthechecksum');
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertFalse($hidden->isValid());
        $this->assertTrue($hidden->isPresent());
        $this->assertTrue(($hidden->getError() instanceof T_Form_Error ));
        $this->assertSame('value',$hidden->getValue());
    }

    function testValidateWithErrorIfValueIsIncorrect()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $src = array($hidden->getFieldname() => 'notthevalue',
                     $hidden->getChecksumFieldname() => $hidden->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertFalse($hidden->isValid());
        $this->assertTrue($hidden->isPresent());
        $this->assertTrue(($hidden->getError() instanceof T_Form_Error ));
        $this->assertSame('value',$hidden->getValue());
    }

    function testValidateWithErrorIfAllInputsMissing()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $cage = new T_Cage_Array(array());
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertFalse($hidden->isValid());
        $this->assertFalse($hidden->isPresent());
        $this->assertTrue(($hidden->getError() instanceof T_Form_Error ));
        $this->assertSame('value',$hidden->getValue());
    }

    function testValidateFailsIfMainValueIsArray()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $src = array($hidden->getFieldname() => array(1,2),
                     $hidden->getChecksumFieldname() => $hidden->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        try {
            $hidden->validate($cage);
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testValidateFailsIfChecksumIsArray()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $src = array($hidden->getFieldname() => $hidden->getFieldValue(),
                     $hidden->getChecksumFieldname() => array(1,2));
        $cage = new T_Cage_Array($src);
        try {
            $hidden->validate($cage);
            $this->fail();
        } catch (T_Exception_Cage $e) { }
    }

    function testErrorIfFailWithFilter()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->attachFilter(new T_Test_Filter_ReverseFailure());
        $src = array($hidden->getFieldname() => $hidden->getFieldValue(),
                     $hidden->getChecksumFieldname() => $hidden->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden,$hidden->validate($cage));
        $this->assertFalse($hidden->isValid());
        $this->assertTrue($hidden->isPresent());
        $this->assertTrue(($hidden->getError() instanceof T_Form_Error ));
        $this->assertSame('value',$hidden->getValue());
    }

    function testCleanValueIsReplacedBySuccessfulValidation()
    {
        $hidden1 = new T_Form_Hidden('alias','value');
        $hidden2 = new T_Form_Hidden('alias','replace');
        $src = array($hidden2->getFieldname() => $hidden2->getFieldValue(),
                     $hidden2->getChecksumFieldname() => $hidden2->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden1,$hidden1->validate($cage));
        $this->assertTrue($hidden1->isValid());
        $this->assertTrue($hidden1->isPresent());
        $this->assertFalse($hidden1->getError());
        $this->assertSame('replace',$hidden1->getValue());
    }

    function testCleanValueIsReplacedBySuccessfulValidationWithSingleFilterApplied()
    {
        $filter = new T_Test_Filter_Suffix();
        $hidden1 = new T_Form_Hidden('alias','value');
        $hidden1->attachFilter($filter);
        $hidden2 = new T_Form_Hidden('alias','replace');
        $hidden2->attachFilter($filter);
        $src = array($hidden2->getFieldname() => $hidden2->getFieldValue(),
                     $hidden2->getChecksumFieldname() => $hidden2->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden1,$hidden1->validate($cage));
        $this->assertTrue($hidden1->isValid());
        $this->assertTrue($hidden1->isPresent());
        $this->assertFalse($hidden1->getError());
        $this->assertSame('replace',$hidden1->getValue());
    }

    function testCleanValueIsReplacedBySuccessfulValidationWithMultipleFilterApplied()
    {
        $filter1 = new T_Test_Filter_Suffix('1st');
        $filter2 = new T_Test_Filter_Suffix('2nd');
        $hidden1 = new T_Form_Hidden('alias','value');
        $hidden1->attachFilter($filter1)->attachFilter($filter2);
        $hidden2 = new T_Form_Hidden('alias','replace');
        $hidden2->attachFilter($filter1)->attachFilter($filter2);
        $src = array($hidden2->getFieldname() => $hidden2->getFieldValue(),
                     $hidden2->getChecksumFieldname() => $hidden2->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $this->assertSame($hidden1,$hidden1->validate($cage));
        $this->assertTrue($hidden1->isValid());
        $this->assertTrue($hidden1->isPresent());
        $this->assertFalse($hidden1->getError());
        $this->assertSame('replace',$hidden1->getValue());
    }

    function testValidatationClearsAnyPreviousError()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->validate(new T_Cage_Array(array())); // error
        $src = array($hidden->getFieldname() => $hidden->getFieldValue(),
                     $hidden->getChecksumFieldname() => $hidden->getChecksumFieldValue());
        $cage = new T_Cage_Array($src);
        $hidden->validate($cage);
        $this->assertTrue($hidden->isValid());
    }

    function testIsValidByDefault()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertTrue($hidden->isValid());
    }

    function testIsNotPresentByDefault()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertFalse($hidden->isPresent());
    }

    function testNoErrorByDefault()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertFalse($hidden->getError());
    }

    function testClearErrorHasAFluentInterface()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertsame($hidden,$hidden->clearError());
    }

    function testClearErrorRemovesAnyError()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->validate(new T_Cage_Array(array()));
        $this->assertFalse($hidden->isValid());
        $hidden->clearError();
        $this->assertTrue($hidden->isValid());
        $this->assertFalse($hidden->getError());
    }

    function testSetFieldnameSaltHasAFluentInterface()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame($hidden,
                $hidden->setFieldnameSalt('salt',new T_Filter_RepeatableHash()));
    }

    function testFieldnameSaltCanBeChanged()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $hidden->setFieldnameSalt('salt',new T_Filter_RepeatableHash());
        $field1 = $hidden->getFieldname();
        $hidden->setFieldnameSalt('change',new T_Filter_RepeatableHash());
        $field2 = $hidden->getFieldname();
        $this->assertNotEquals($field1,$field2);
    }

    function testFieldnameIsAliasWithNoSalt()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertSame($hidden->getFieldname(),'alias');
    }

    function testFieldnameDependsOnSalt()
    {
        $hidden1 = new T_Form_Hidden('alias','value');
        $hidden1->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $hidden2 = new T_Form_Hidden('alias','value');
        $hidden2->setFieldnameSalt('salt2',new T_Filter_RepeatableHash());
        $this->assertNotEquals($hidden1->getFieldname(),$hidden1->getAlias());
        $this->assertNotEquals($hidden2->getFieldname(),$hidden1->getFieldname());
    }

    function testChecksumFieldnameIsDerivedFromAliasWithNoSalt()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertNotEquals($hidden->getFieldname(),$hidden->getChecksumFieldname());
        $this->assertContains('alias',$hidden->getChecksumFieldname());
    }

    function testChecksumFieldnameDependsOnSalt()
    {
        $hidden1 = new T_Form_Hidden('alias','value');
        $hidden1->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $hidden2 = new T_Form_Hidden('alias','value');
        $hidden2->setFieldnameSalt('salt2',new T_Filter_RepeatableHash());
        $this->assertNotEquals($hidden1->getFieldname(),$hidden1->getChecksumFieldname());
        $this->assertNotEquals($hidden2->getChecksumFieldname(),$hidden1->getChecksumFieldname());
    }

    function testChecksumFieldValueIsHexHash()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $this->assertTrue(ctype_alnum($hidden->getChecksumFieldValue()));
    }

    function testChecksumFieldValueDependsOnValue()
    {
        $hidden1 = new T_Form_Hidden('alias','value1');
        $hidden2 = new T_Form_Hidden('alias','value2');
        $this->assertNotEquals($hidden1->getChecksumFieldValue(),$hidden2->getChecksumFieldValue());
    }

    function testChecksumFieldValueDependsOnSalt()
    {
        $hidden1 = new T_Form_Hidden('alias','value');
        $hidden2 = new T_Form_Hidden('alias','value');
        $hidden1->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $hidden2->setFieldnameSalt('salt2',new T_Filter_RepeatableHash());
        $this->assertNotEquals($hidden1->getChecksumFieldValue(),$hidden2->getChecksumFieldValue());
    }

    function testCanAcceptAVisitor()
    {
        $hidden = new T_Form_Hidden('alias','value');
        $visitor = new T_Test_VisitorStub();
        $hidden->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$hidden);
        $this->assertTrue(count($visited['object'])==1);
    }

    // ATTRIBUTES

    function testSetAttributeAddsAnAttribute()
    {
        $input = new T_Form_Hidden('alias','value');
        $this->assertSame($input->setAttribute('name','value'),$input,'fluent');
        $this->assertSame($input->getAttribute('name'),'value');
        $this->assertSame($input->getAllAttributes(),array('name'=>'value'));
    }

    function testSetAttributeCanBeUsedToAddMultipleAttributes()
    {
        $input = new T_Form_Hidden('alias','value');
        $input->setAttribute('name1','value1')
              ->setAttribute('name2','value2');
        $this->assertSame($input->getAttribute('name1'),'value1');
        $this->assertSame($input->getAttribute('name2'),'value2');
        $this->assertSame($input->getAllAttributes(),array('name1'=>'value1',
                                                           'name2'=>'value2')  );
    }

    function testSetAttributeCanOverwriteExistingAttributes()
    {
        $input = new T_Form_Hidden('alias','value');
        $input->setAttribute('name','value1')
              ->setAttribute('name','value2');
        $this->assertSame($input->getAttribute('name'),'value2');
        $this->assertSame($input->getAllAttributes(),array('name'=>'value2'));
    }

    function testGetAllAttributesReturnsEmptyArrayByDefault()
    {
        $input = new T_Form_Hidden('alias','value');
        $this->assertSame($input->getAllAttributes(),array());
    }

    function testGetAttributeReturnsNullIfNameNotSet()
    {
        $input = new T_Form_Hidden('alias','value');
        $this->assertSame($input->getAttribute('name'),null);
    }

}
