<?php
abstract class T_Test_Form_ContainerHarness
         extends T_Test_Form_Group
{

    function testForwardIsNullByDefault()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertTrue(is_null($input->getForward()));
    }

    function testCanSetForwardAsAUrl()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $url = new T_Url('http','example.com');
        $input->setForward($url);
        $this->assertSame($url,$input->getForward());
    }

    function testSetForwardMethodHasAFluentInterface()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->setForward(new T_Url('http','example.com'));
        $this->assertSame($input,$test);
    }

    function testFormCharsetIsDefaultCharset()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertSame(T_CHARSET,$input->getCharset());
    }

    function testDefaultFormMimeEncodingType()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $expect = 'application/x-www-form-urlencoded';
        $this->assertSame($expect,$input->getMimeString());
    }

    function testCanAddButtonAction()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $button1 = new T_Form_Button('b1','label');
        $input->addAction($button1);
        $this->assertSame(array('b1'=>$button1),$input->getActions());
        $button2 = new T_Form_Button('b2','label');
        $input->addAction($button2);
        $this->assertSame(array('b1'=>$button1,'b2'=>$button2),
                          $input->getActions()  );
    }

    function testAddActionMethodHasAFluentInterface()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->addAction(new T_Form_Button('b1','label'));
        $this->assertSame($input,$test);
    }

    function testIsActionIsFalseByDefault()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $test = $input->addAction(new T_Form_Button('b1','label'));
        $this->assertFalse($input->isAction('b1'));
    }

    function testValidateAlsoValidatesSingleButton()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->addAction(new T_Form_Button('b1','label'));
        $input->validate(new T_Cage_Array(array('b1'=>'')));
        $this->assertTrue($input->isAction('b1'));
    }

    function testValidateAlsoValidatesMultipleButton()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->addAction(new T_Form_Button('b1','label'));
        $input->addAction(new T_Form_Button('b2','label'));
        $input->validate(new T_Cage_Array(array('b2'=>'')));
        $this->assertTrue($input->isAction('b2'));
        $this->assertFalse($input->isAction('b1'));
    }

    function testButtonValidationStopsAfterAnActionIsFound()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $input->addAction(new T_Form_Button('b1','label'));
        $input->addAction(new T_Form_Button('b2','label'));
        $input->validate(new T_Cage_Array(array('b1'=>'','b2'=>'')));
        $this->assertFalse($input->isAction('b2')); /* not validated */
        $this->assertTrue($input->isAction('b1'));  /* found, and stopped */
    }

    function testIsActionMethodReturnFalseIfNoSuchAction()
    {
        $input = $this->getInputCollection('myalias','mylabel');
        $this->assertFalse($input->isAction('not_an_action'));
    }

    function testIsPresentIncludesActions()
    {
        $form = $this->getInputCollection('parent','mylabel');
        $form->addChild(new T_Test_Form_ElementStub('child1','label'));
        $form->addAction(new T_Form_Button('button1','Button 1 Label'));
        $form->addAction(new T_Form_Button('button2','Button 1 Label'));
        /* not present */
        $source = new T_Cage_Array(array());
        $form->validate($source);
        $this->assertFalse($form->isPresent());
        /* action only present */
        $source = new T_Cage_Array(array('button2'=>''));
        $form->validate($source);
        $this->assertTrue($form->isPresent());
    }

    function testIsSubmittedWhenActionsArePresent()
    {
        $form = $this->getInputCollection('parent','mylabel');
        $form->addChild(new T_Test_Form_ElementStub('child1','label'));
        $form->addAction(new T_Form_Button('button1','Button 1 Label'));
        $form->addAction(new T_Form_Button('button2','Button 1 Label'));
        /* not present */
        $source = new T_Cage_Array(array());
        $this->assertFalse($form->isSubmitted($source));
        /* not present even when element is present */
        $source = new T_Cage_Array(array('child1'=>'value'));
        $this->assertFalse($form->isSubmitted($source));
        /* action only present */
        $source = new T_Cage_Array(array('button2'=>''));
        $this->assertTrue($form->isSubmitted($source));
    }

    function testFormMimeIsUrlEncodedInNormalForm()
    {
        $form = $this->getInputCollection('myalias','mylabel');
        $form->addChild(new T_Form_Text('child','label'));
        $this->assertSame('application/x-www-form-urlencoded',
                          $form->getMimeString() );
    }

    function testFormMimeIsMultipartWithFileUpload()
    {
        $form = $this->getInputCollection('myalias','mylabel');
        $form->addChild(new T_Form_Text('child','label'));
        $form->addChild(new T_Form_Upload('file','label'));
        $this->assertSame('multipart/form-data',$form->getMimeString());
    }

    function testActionsAreSalted()
    {
        $form = $this->getInputCollection('myalias','mylabel');
        $action = new T_Form_Button('button','label');
        $form->addAction($action);
        $form->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertNotEquals('button',$action->getFieldname());
    }

    function testCanSwitchToRequiredCollection()
    {
        try {
            parent::testCanSwitchToRequiredCollection();
            $this->fail();
        } catch (BadFunctionCallException $e) { }
    }

    function testSetRequiredMethodHasFluentInterface()
    {
        try {
            parent::testSetRequiredMethodHasFluentInterface();
            $this->fail();
        } catch (BadFunctionCallException $e) { }
    }

    function testErrorIfCollectionIsRequiredAndNotPresent()
    {
        try {
            parent::testErrorIfCollectionIsRequiredAndNotPresent();
            $this->fail();
        } catch (BadFunctionCallException $e) { }
    }

    function testNoErrorIfCollectionIsRequiredAndIsPresent()
    {
        try {
            parent::testNoErrorIfCollectionIsRequiredAndIsPresent();
            $this->fail();
        } catch (BadFunctionCallException $e) { }
    }

}
