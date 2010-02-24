<?php
class T_Test_Form_GetHandler extends T_Unit_Case
{

    function getEnvironment($get=array())
    {
        return new T_Test_EnvironmentStub(
                        array('GET'=>new T_Cage_Array($get))
                        );
    }

    function testThatFormIsSetInConstuctor()
    {
        $form = new T_Form_Get('myform','Test Form');
        $filter = new T_Form_GetHandler($form,$this->getEnvironment());
        $this->assertSame($form,$filter->getForm());
    }

    function testThatPreFilterValidatesFormIfPresent()
    {
        $get = array('element'=>'value');
        $env = $this->getEnvironment($get);
        $form = new T_Form_Get('myform','Test Form');
        $form->addChild(new T_Test_Form_ElementStub('element','Label'));
        $filter = new T_Form_GetHandler($form,$env);
        $response = new T_Response();
        $filter->preFilter($response);
        $this->assertTrue($form->isPresent());
        $this->assertTrue($form->element->isValidated());
    }

    function testThatPreFilterDoesNotValidateFormIfNotPresent()
    {
        $env = $this->getEnvironment();
        $form = new T_Form_Get('myform','Test Form');
        $form->addChild(new T_Test_Form_ElementStub('element','Label'));
        $filter = new T_Form_GetHandler($form,$env);
        $response = new T_Response();
        $filter->preFilter($response);
        $this->assertFalse($form->isPresent());
        $this->assertFalse($form->element->isValidated());
    }

}
