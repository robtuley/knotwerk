<?php
class T_Test_Form_XhtmlError extends T_Unit_Case
{

    /**
     * Gets a test form.
     *
     * @return T_Form_Get  test form
     */
    protected function getTestForm()
    {
        $form = new T_Form_Get('test','A Test Form');
        $form->addChild(new T_Form_Fieldset('contact','Contact Details'));
        $form->contact->addChild(new T_Form_Text('name','Name'));
        $form->contact->name->setOptional();
        $form->contact->addChild(new T_Form_Text('email','Email'));
        $form->contact->email->setOptional();
        $form->contact->addChild(new T_Form_Upload('upload','Profile Image'));
        $form->contact->upload->setOptional();
        $form->addChild(new T_Form_Fieldset('register','Registration Details'));
        $form->register->addChild(new T_Form_Password('passwd','Password'));
        $form->register->passwd->setOptional();
        return $form;
    }

    /**
     * Converts the form to XML with visitor.
     *
     * @param T_Form_Container $form  form to render
     * @param T_Visitor $visitor  optional modified visitor
     * @return string  form XHTML
     */
    protected function getFormXml(T_Form_Container $form,$visitor=null)
    {
        if (is_null($visitor)) {
            $visitor = new T_Form_XhtmlError();
        }
        $form->accept($visitor);
        return $visitor->__toString();
    }

    function testNoErrorsProducesZeroLengthString()
    {
        $xml = $this->getFormXml($this->getTestForm());
        $this->assertSame('',$xml);
    }

    function testFormWithSingleErrorProducesValidXhtml()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = $this->getFormXml($form);
        $dom = new DOMDocument;
        $this->assertTrue($dom->loadXML($xml));
    }

    function testFormWithMutipleErrorProducesValidXhtml()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error & msg'));
        $form->register->passwd->setError(new T_Form_Error('error & msg'));
        $xml = $this->getFormXml($form);
        $dom = new DOMDocument;
        $this->assertTrue($dom->loadXML($xml));
    }

    function testErrorSummaryInDivWithClassError()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame('error', (string) $xml['class']);
    }

    function testErrorSummaryInDivWithParagraphIntro()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertTrue(isset($xml->p));
        $this->assertTrue(strlen($xml->p)>0);
    }

    function testErrorSummaryWithSingleErrorHasSingleErrorPoint()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(1,count($xml->ul->li));
    }

    function testErrorSummaryWithSingleErrorHasLinkToField()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame('#'.$form->contact->name->getAlias(),
                          (string) $xml->ul->li->a['href'] );
    }

    function testErrorSummaryWithSingleErrorLinkTextIsLabel()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame($form->contact->name->getLabel(),
                          (string) $xml->ul->li->a );
    }

    function testErrorSummaryWithSingleErrorContainsErrorMessage()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertContains('error msg',
                              (string) $xml->ul->li );
    }

    function testFormLevelErrorSummaryContainsErrorMessageButNoLink()
    {
        $form = $this->getTestForm();
        $form->setError(new T_Form_Error('error msg'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertContains('error msg',
                              (string) $xml->ul->li );
        $this->assertSame('',
                          (string) $xml->ul->li->a );
    }

    function testErrorSummaryWithMultipleErrorHasMultipleErrorPoint()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error 1'));
        $form->register->passwd->setError(new T_Form_Error('error 2'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(2,count($xml->ul->li));
    }

    function testErrorSummaryWithMultipleErrorHasLinksToField()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error 1'));
        $form->register->passwd->setError(new T_Form_Error('error 2'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame('#'.$form->contact->name->getAlias(),
                          (string) $xml->ul->li[0]->a['href'] );
        $this->assertSame('#'.$form->register->passwd->getAlias(),
                          (string) $xml->ul->li[1]->a['href'] );
    }

    function testErrorSummaryWithMultipleErrorLinkTextIsLabel()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error 1'));
        $form->register->passwd->setError(new T_Form_Error('error 2'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame($form->contact->name->getLabel(),
                          (string) $xml->ul->li[0]->a );
        $this->assertSame($form->register->passwd->getLabel(),
                          (string) $xml->ul->li[1]->a );
    }

    function testErrorSummaryWithMultipleErrorContainsErrorMessage()
    {
        $form = $this->getTestForm();
        $form->contact->name->setError(new T_Form_Error('error & 1'));
        $form->register->passwd->setError(new T_Form_Error('error & 2'));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertContains('error & 1',
                              (string) $xml->ul->li[0] );
        $this->assertContains('error & 2',
                              (string) $xml->ul->li[1] );
    }

    function testErrorSummaryWithSingleErrorHasNoTrailingParagraphByDefault()
    {
        $form = $this->getTestForm();
        $form->contact->name->attachFilter(new T_Test_Filter_Failure());
        $form->validate(new T_Cage_Array(array('name'=>'a','email'=>'b')));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(1,count($xml->p));
    }

    function testErrorSummaryWithMultipleErrorHasNoTrailingParagraphByDefault()
    {
        $form = $this->getTestForm();
        $form->contact->name->attachFilter(new T_Test_Filter_Failure());
        $form->contact->email->attachFilter(new T_Test_Filter_Failure());
        $form->validate(new T_Cage_Array(array('name'=>'a','email'=>'b')));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(1,count($xml->p));
    }

    function testPasswdWithErrorHasNoTrailingParagraphByDefault()
    {
        $form = $this->getTestForm();
        $form->contact->name->attachFilter(new T_Test_Filter_Failure());
        $form->register->passwd->attachFilter(new T_Test_Filter_Failure());
        $form->validate(new T_Cage_Array(array('name'=>'a','email'=>'b')));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(1,count($xml->p));
    }

    function testSummaryWithValidPasswordNotesNeedsToBeRecompleted()
    {
        $form = $this->getTestForm();
        $form->contact->name->attachFilter(new T_Test_Filter_Failure());
        $form->contact->email->attachFilter(new T_Test_Filter_Failure());
        $data = array('name'=>'a','email'=>'b','passwd'=>'c');
        $form->validate(new T_Cage_Array($data));
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(2,count($xml->p));
        $this->assertTrue(strlen($xml->p[1])>0);
        $this->assertSame('#'.$form->register->passwd->getAlias(),
                          (string) $xml->p[1]->a['href'] );
        $this->assertSame($form->register->passwd->getLabel(),
                          (string) $xml->p[1]->a );
    }

    function testSummaryWithValidPasswordAndUploadNotesNeedsToBeRecompleted()
    {
        $form = $this->getTestForm();
        $form->contact->name->attachFilter(new T_Test_Filter_Failure());
        $data = array('name'=>'a','passwd'=>'c');
        $files = array('upload' => new T_File_Uploaded('some/path',100,'upload.txt'));
        $post = new T_Test_Cage_PostStub($data,$files);
        $form->validate($post);
        $xml = new T_Xml_Element($this->getFormXml($form));
        $this->assertSame(2,count($xml->p));
        $this->assertTrue(strlen($xml->p[1])>0);
        $this->assertSame('#'.$form->register->passwd->getAlias(),
                          (string) $xml->p[1]->a[1]['href'] );
        $this->assertSame($form->register->passwd->getLabel(),
                          (string) $xml->p[1]->a[1] );
        $this->assertSame('#'.$form->contact->upload->getAlias(),
                          (string) $xml->p[1]->a[0]['href'] );
        $this->assertSame($form->contact->upload->getLabel(),
                          (string) $xml->p[1]->a[0] );
    }

}
