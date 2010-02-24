<?php
class T_Test_Form_Group extends T_Unit_Case
{

    protected function getInputCollection($alias,$label)
    {
        return new T_Form_Group($alias,$label);
    }

    // tests

    function testAliasSetThroughConstructor()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame($input->getAlias(),'myalias');
    }

    function testAliasCanBeChanged()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame($input,$input->setAlias('new'));
        $this->assertSame($input->getAlias(),'new');
    }

    function testLabelSetThroughConstructor()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame($input->getLabel(),'mylabel');
    }

    function testFilterCanBePassedToGetLabelMethod()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $f =  new T_Test_Filter_Suffix();
        $this->assertSame($input->getLabel($f),$f->transform('mylabel'));
    }

    function testAliasCastToStringInConstructor()
    {
        $input = $this->getInputCollection(123,'mylabel');
        $this->assertSame($input->getAlias(),'123');
    }

    function testLabelCastToStringInConstructor()
    {
        $input = $this->getInputCollection('myalias',123);
        $this->assertSame($input->getLabel(),'123');
    }

    function testConstructorFailureWithZeroLengthAlias()
    {
        try {
            $input = $this->getInputCollection(null,'mylabel');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testConstructorFailureWithZeroLengthLabel()
    {
        try {
            $input = $this->getInputCollection('myalias',null);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testCollectionHasNoChildrenByDefault()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $this->assertFalse($input->isChildren());
    }

    function testIsChildrenReturnsTrueOnceChildIsAdded()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $input->addChild(new T_Test_Form_ElementStub('child','childlabel'));
        $this->assertTrue($input->isChildren());
    }

    function testGetCompositeReturnsObjectReference()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $test = $input->getComposite();
        $this->assertSame($test,$input);
    }

    function testFailureWhenAccessNotExistingChildElement()
    {
        $input = $this->getInputCollection('parent','mylabel');
        try {
            $child = $input->notachild;
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testWhenAddChildWithoutKeyAliasIsUsed()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child = new T_Test_Form_ElementStub('childalias','label');
        $input->addChild($child);
        $this->assertSame($child,$input->childalias);
    }

    function testCanOverrideAliasWithExplicitChildKey()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child = new T_Test_Form_ElementStub('childalias','label');
        $input->addChild($child,'newkey');
        $this->assertSame($child,$input->newkey);
        try {
            $child = $input->childalias;
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testAddChildMethodHasAFluentInterface()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $test = $input->addChild(new T_Test_Form_ElementStub('child','label'));
        $this->assertSame($input,$test);
    }

    function testCanAddAndAccessMultipleChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $this->assertSame($child1,$input->child1);
        $this->assertSame($child2,$input->child2);
    }

    function testCollectionVisitsItselfOnlyWithNoChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $visitor = new T_Test_VisitorStub();
        $input->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['object'][0],$input);
        $this->assertTrue(count($visited['object'])==1);
    }

    function testAcceptMethodHasFluentInterface()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $visitor = new T_Test_VisitorStub();
        $test = $input->accept($visitor);
        $this->assertSame($input,$test);
    }

    function testCollectionVisitedAtCorrectDepthWithNoChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $visitor = new T_Test_VisitorStub();
        $input->accept($visitor);
        $visited = $visitor->getVisited();
        $this->assertSame($visited['depth'][0],0);
    }

    function testCollectionVisitsItselfAndChildrenWhenTraverseOn()
    {
        /* create composite */
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        /* visit parent */
        $visitor = new T_Test_VisitorStub();
        $input->accept($visitor);
        $visited = $visitor->getVisited();
        /* check objects visited */
        $this->assertTrue(count($visited['object'])==3);
        $this->assertSame($visited['object'][0],$input);
        $this->assertSame($visited['object'][1],$child1);
        $this->assertSame($visited['object'][2],$child2);
        /* check depths visited. */
        $this->assertSame($visited['depth'][0],0);
        $this->assertSame($visited['depth'][1],1);
        $this->assertSame($visited['depth'][2],1);
    }

    function testCollectionVisitsItselfNotChildrenWhenTraverseOff()
    {
        /* create composite */
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        /* visit parent */
        $visitor = new T_Test_VisitorStub();
        $visitor->setTraverseChildren(false);
        $input->accept($visitor);
        $visited = $visitor->getVisited();
        /* check objects visited */
        $this->assertTrue(count($visited['object'])==1);
        $this->assertSame($visited['object'][0],$input);
        $this->assertSame($visited['depth'][0],0);
    }

    function testGetValueMethodReturnsNullByDefault()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $this->assertTrue(is_null($input->getValue()));
    }

    function testValidateMethodHasFluentInterface()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $test = $input->validate(new T_Cage_Array(array()));
        $this->assertSame($input,$test);
    }

    function testValidateMethodCascadesToChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('parent'=>'')));
        $this->assertTrue($child1->isValidated());
        $this->assertTrue($child2->isValidated());
    }

    function testCollectionIsValidWithNoChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $this->assertTrue($input->isValid());
        $input->validate(new T_Cage_Array(array('parent'=>'')));
        $this->assertTrue($input->isValid());
    }

    function testCollectionIsValidWithAllValidChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child1->setOptional();
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $child2->setOptional();
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('parent'=>'')));
        $this->assertTrue($input->isValid());
    }

    function testCollectionIsNotValidWithFirstChildInvalid()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $child2->setOptional();
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('parent'=>'')));
        $this->assertFalse($input->isValid());
    }

    function testCollectionIsNotValidWithLastChildInvalid()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child1->setOptional();
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('parent'=>'')));
        $this->assertFalse($input->isValid());
    }

    function testCollectionIsNotValidWithAllChildInvalid()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('parent'=>'')));
        $this->assertFalse($input->isValid());
    }

    function testCollectionIsNotPresentIfNoChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $this->assertFalse($input->isPresent());
        $input->validate(new T_Cage_Array(array()));
        $this->assertFalse($input->isPresent());
    }

    function testCollectionNotPresentIfNoChildrenPresent()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array()));
        $this->assertFalse($input->isPresent());
    }

    function testCollectionIsPresentIfFirstChildPresent()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('child1'=>'value','parent'=>'')));
        $this->assertTrue($input->isPresent());
    }

    function testCollectionIsPresentIfLastChildPresent()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->validate(new T_Cage_Array(array('child2'=>'value','parent'=>'')));
        $this->assertTrue($input->isPresent());
    }

    function testCollectionIsPresentIfAllChildrenPresent()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $source = new T_Cage_Array(array('child2'=>'a','child2'=>'b','parent'=>''));
        $input->validate($source);
        $this->assertTrue($input->isPresent());
    }

    function testSetFieldnameSaltHasFluentInterface()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $test = $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($input,$test);
    }

    function testSetFieldnameSaltCascadesToChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame('mysalt',$child1->getFieldnameSalt());
        $this->assertSame('mysalt',$child2->getFieldnameSalt());
    }

    function testSearchForElementReturnsCollectionIfMatchesAlias()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame($input,$input->search('myalias'));
    }

    function testSearchFalseIfAliasDoesNotMatchCollectionWithNoChildren()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertFalse($input->search('diffalias'));
    }

    function testSearchIsCaseSenstiveForAliasCheck()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertFalse($input->search('mYalias'));
    }

    function testSearchFalseIfAliasDoesNotMatchCollectionOrChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $this->assertFalse($input->search('diffalias'));
    }

    function testSearchRetrievesMatchingFirstChild()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $this->assertSame($input->search('child1'),$child1);
    }

    function testSearchRetrievesMatchingLastChild()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $this->assertSame($input->search('child2'),$child2);
    }

    function testSearchCanMatchChildrenKeyWhenNotSameAsAlias()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1,'key1')->addChild($child2);
        $this->assertSame($input->search('key1'),$child1);
        $this->assertSame($input->search('child1'),$child1);
    }

    function testIsNotSubmittedIfEmptyCollection()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $this->assertFalse($input->isSubmitted(new T_Cage_Array(array())));
    }

    function testIsNotSubmittedIfNoChildrenAreSubmitted()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $this->assertFalse($input->isSubmitted(new T_Cage_Array(array())));
    }

    function testIsSubmittedIfAnyChildrenAreSubmitted()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child1 = new T_Test_Form_ElementStub('child1','label');
        $child2 = new T_Test_Form_ElementStub('child2','label');
        $input->addChild($child1)->addChild($child2);
        $source = new T_Cage_Array(array('child2'=>'value','parent'=>''));
        $this->assertTrue($input->isSubmitted($source));
    }

    function testGetErrorIsFalseByDefault()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame(false,$input->getError());
    }

    function testCanSetSpecificError()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $error = new T_Form_Error('test');
        $input->setError($error);
        $this->assertSame($error,$input->getError());
    }

    function testThatSetErrorHasAFluentInterface()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->setError(new T_Form_Error('test'));
        $this->assertSame($test,$input);
    }

    function testThatSetErrorAffectsWhetherCollectionIsValid()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->setError(new T_Form_Error('test'));
        $this->assertFalse($input->isValid());
    }

    function testCanClearErrorOnCollection()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->setError(new T_Form_Error('test'));
        $input->clearError();
        $this->assertTrue($input->isValid());
        $this->assertFalse($input->getError());
    }

    function testClearErrorCascadesToCollectionChildren()
    {
        $parent = $this->getInputCollection('parent','Parent Label');
        $child = $this->getInputCollection('child','Child Label');
        $child->setError(new T_Form_Error('test'));
        $parent->addChild($child);
        $this->assertFalse($parent->isValid());
        $parent->clearError();
        $this->assertTrue($parent->isValid());
    }

    function testIsNotRequiredByDefault()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertFalse($input->isRequired());
    }

    function testCanSwitchToRequiredCollection()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->setRequired();
        $this->assertTrue($input->isRequired());
    }

    function testSetRequiredMethodHasFluentInterface()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->setRequired();
        $this->assertSame($test,$input);
    }

    function testErrorIfCollectionIsRequiredAndNotPresent()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $input->setRequired()
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array()));
        $this->assertFalse($input->isValid());
        $this->assertTrue($input->getError() instanceof T_Form_Error);
    }

    function testNoErrorIfCollectionIsRequiredAndIsPresent()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $input->setRequired()
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertTrue($input->isValid());
        $this->assertSame($input->getError(),false);
    }

    function testValueIsNullByDefault()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame(null,$input->getValue());
    }

    function testCanSetValue()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->setValue('value');
        $this->assertSame('value',$input->getValue());
    }

    function testSetValueMethodHasAFluentInterface()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->setValue('value');
        $this->assertSame($test,$input);
    }

    function testAttachFilterMethodHasFluentInterface()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->attachFilter(new T_Test_Filter_Failure());
        $this->assertSame($test,$input);
    }

    function testWhenNotPresentFiltersNotExecuted()
    {
        $filter = new T_Test_Form_CollectionFilterStub();
        $input = $this->getInputCollection('myalias','mylabel');
        $input->attachFilter($filter);
        $input->validate(new T_Cage_Array(array()));
        $this->assertSame(null,$filter->getFilterValue());
    }

    function testIsPresentSingleFilterIsExecuted()
    {
        $filter = new T_Test_Form_CollectionFilterStub();
        $input = $this->getInputCollection('parent','label');
        $input->attachFilter($filter)
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertSame($input,$filter->getFilterValue());
    }

    function testIsPresentMultipleFiltersAreExecuted()
    {
        $filter1 = new T_Test_Form_CollectionFilterStub();
        $filter2 = new T_Test_Form_CollectionFilterStub();
        $input = $this->getInputCollection('parent','label');
        $input->attachFilter($filter1)
              ->attachFilter($filter2)
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertSame($input,$filter1->getFilterValue());
        $this->assertSame($input,$filter2->getFilterValue());
    }

    function testIsInvalidIfSingleFilterFails()
    {
        $input = $this->getInputCollection('parent','label');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertFalse($input->isValid());
        $this->assertTrue($input->getError() instanceof T_Form_Error);
    }

    function testIsInvalidIfFirstFilterFails()
    {
        $input = $this->getInputCollection('parent','label');
        $input->attachFilter(new T_Test_Filter_Failure())
              ->attachFilter(new T_Test_Form_CollectionFilterStub())
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertFalse($input->isValid());
        $this->assertTrue($input->getError() instanceof T_Form_Error);
    }

    function testIsInvalidIfLastFilterFails()
    {
        $input = $this->getInputCollection('parent','label');
        $input->attachFilter(new T_Test_Form_CollectionFilterStub())
              ->attachFilter(new T_Test_Filter_Failure())
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertFalse($input->isValid());
        $this->assertTrue($input->getError() instanceof T_Form_Error);
    }

    function testCanRemoveFilterWhenExactlyMatched()
    {
        $filter1 = new T_Test_Form_CollectionFilterStub();
        $filter2 = new T_Test_Form_CollectionFilterStub();
        $input = $this->getInputCollection('parent','label');
        $input->attachFilter($filter1)
              ->attachFilter($filter2)
              ->removeFilter($filter1)
              ->addChild(new T_Test_Form_ElementStub('child','label'));
        $input->validate(new T_Cage_Array(array('child'=>'value','parent'=>'')));
        $this->assertSame(null,$filter1->getFilterValue());
        $this->assertSame($input,$filter2->getFilterValue());
    }

    function testExceptionWhenTryToRemoveNonExistingFilter()
    {
        $filter = new T_Test_Form_CollectionFilterStub();
        $input = $this->getInputCollection('parent','label');
        try {
            $input->removeFilter($filter);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testThatValueIsClearedByValidation()
    {
        $input = $this->getInputCollection('myalias','label');
        $input->setValue('value')
              ->validate(new T_Cage_Array(array('myalias'=>'')));
        $this->assertSame(null,$input->getValue());
    }

    function testThatErrorIsClearedByValidation()
    {
        $input = $this->getInputCollection('myalias','label');
        $input->setError(new T_Form_Error('some error'))
              ->validate(new T_Cage_Array(array('myalias'=>'')));
        $this->assertTrue($input->isValid());
        $this->assertSame(false,$input->getError());
    }

    // ATTRIBUTES

    function testSetAttributeAddsAnAttribute()
    {
        $input = $this->getInputCollection('alias','label');
        $this->assertSame($input->setAttribute('name','value'),$input,'fluent');
        $this->assertSame($input->getAttribute('name'),'value');
        $this->assertSame($input->getAllAttributes(),array('name'=>'value'));
    }

    function testSetAttributeCanBeUsedToAddMultipleAttributes()
    {
        $input = $this->getInputCollection('alias','label');
        $input->setAttribute('name1','value1')
              ->setAttribute('name2','value2');
        $this->assertSame($input->getAttribute('name1'),'value1');
        $this->assertSame($input->getAttribute('name2'),'value2');
        $this->assertSame($input->getAllAttributes(),array('name1'=>'value1',
                                                           'name2'=>'value2')  );
    }

    function testSetAttributeCanOverwriteExistingAttributes()
    {
        $input = $this->getInputCollection('alias','label');
        $input->setAttribute('name','value1')
              ->setAttribute('name','value2');
        $this->assertSame($input->getAttribute('name'),'value2');
        $this->assertSame($input->getAllAttributes(),array('name'=>'value2'));
    }

    function testGetAllAttributesReturnsEmptyArrayByDefault()
    {
        $input = $this->getInputCollection('alias','label');
        $this->assertSame($input->getAllAttributes(),array());
    }

    function testGetAttributeReturnsNullIfNameNotSet()
    {
        $input = $this->getInputCollection('alias','label');
        $this->assertSame($input->getAttribute('name'),null);
    }

    // first child

    function testFirstChildIsReturnedWithMultipleChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $input->addChild($child=new T_Test_Form_ElementStub('child1','childlabel'));
        $input->addChild(new T_Test_Form_ElementStub('child2','childlabel'));
        $this->assertSame($child,$input->getFirstChild());
    }

    function testFalseIsReturnedFromGetFirstChildIfNoChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $this->assertFalse($input->getFirstChild());
    }

    function testCloneIsCascadedToChildren()
    {
        $input = $this->getInputCollection('parent','mylabel');
        $child = new T_Test_Form_ElementStub('childalias','label');
        $input->addChild($child);
        $this->assertSame($child,$input->childalias);
        $clone = clone($input);
        $this->assertNotSame($child,$clone->childalias);
        $this->assertEquals($child,$clone->childalias);
    }

}
