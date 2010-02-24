<?php
class T_Test_Form_PostHandler extends T_Unit_Case
{

    function getEnvironment($method,$post=array(),$get=array())
    {
        $env = new T_Test_EnvironmentStub(
                        array('POST'=>new T_Cage_Array($post),
                              'GET'=>new T_Cage_Array($get) )
                        );
        $env->setRequest(new T_Url('http','example.com'),$method);
        return $env;
    }

    /**
     * Gets all form checking hidden inputs.
     *
     * @param T_Form_Container $form
     * @return array
     */
    function getActionSaltTimeoutAndLockArray(T_Form_Container $form)
    {
        $alias = $form->getAlias();
        $salt = $form->search("{$alias}_salt");
        $timeout = $form->search("{$alias}_timeout");
        $lock = $form->search("{$alias}_thread_lock");
        $data = array( $salt->getFieldname() => $salt->getFieldValue(),
                       $salt->getChecksumFieldname() => $salt->getChecksumFieldValue(),
                       $timeout->getFieldname() => $timeout->getFieldValue(),
                       $timeout->getChecksumFieldname() => $timeout->getChecksumFieldValue());
        if ($lock) {
            $data[$lock->getFieldname()] = $lock->getFieldValue();
            $data[$lock->getChecksumFieldname()] = $lock->getChecksumFieldValue();
        }
        $actions = $form->getActions();
        $button = reset($actions);
        $data[$button->getFieldname()] = '';
        return $data;
    }

    function testFormIsValidIfSubmittedWithinTimeoutWithNoThreadLock()
    {
        // GET request
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('GET');
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash());
        $filter->preFilter($response=new T_Test_ResponseStub()); // add checking inputs
        $filter->postFilter($response);
        $post = $this->getActionSaltTimeoutAndLockArray($form);
        // POST request
        $env = $this->getEnvironment('POST',$post);
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash());
        $filter->preFilter($response=new T_Test_ResponseStub());
        $this->assertTrue($form->isPresent(),'form is present');
        $this->assertTrue($form->isValid(),'form is valid');
    }

    function testFormIsValidButNotPresentIfNotSubmitted()
    {
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('POST');
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash());
        $filter->preFilter($response=new T_Test_ResponseStub());
        $this->assertFalse($form->isPresent(),'form not present');
        $this->assertTrue($form->isValid(),'form is valid');
    }

    function testFormIsValidIfSubmittedWithinTimeoutWithSameThreadLock()
    {
        // GET
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('GET');
        $filter = new T_Form_PostHandler($form,$env,
                            new T_Filter_RepeatableHash(),'lock');
        $filter->preFilter($response=new T_Test_ResponseStub()); // add checking inputs
        $filter->postFilter($response);
        $post = $this->getActionSaltTimeoutAndLockArray($form);
        // POST
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('POST',$post);
        $filter = new T_Form_PostHandler($form,$env,
                            new T_Filter_RepeatableHash(),'lock');
        $filter->preFilter($response=new T_Test_ResponseStub());
        $this->assertTrue($form->isPresent(),'form is present');
        $this->assertTrue($form->isValid(),'form is valid');
    }

    function testFormIsInValidIfSubmittedWithinTimeoutWithDiffThreadLock()
    {
        // GET
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('GET');
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash(),'lock1');
        $filter->preFilter($response=new T_Test_ResponseStub()); // add checking inputs
        $filter->postFilter($response);
        $post = $this->getActionSaltTimeoutAndLockArray($form);

        // POST
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('POST',$post);
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash(),'lock2');
        $filter->preFilter($response=new T_Test_ResponseStub());
        $this->assertTrue($form->isPresent(),'form is present');
        $this->assertFalse($form->isValid(),'form is NOT valid');
    }

    function testFormIsInValidIfTimeStampExpires()
    {
        // GET
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('GET');
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash(),'lock',-1);
           // tiemstamp of -1 means form has already run out!!
        $filter->preFilter($response=new T_Test_ResponseStub()); // add checking inputs
        $filter->postFilter($response);
        $post = $this->getActionSaltTimeoutAndLockArray($form);
        // POST
        $form = new T_Form_Post('test','label');
        $form->setForward(new T_Url('http','example.com'));
        $env = $this->getEnvironment('POST',$post);
        $filter = new T_Form_PostHandler($form,$env,new T_Filter_RepeatableHash(),'lock');
        $filter->preFilter($response=new T_Test_ResponseStub());
        $this->assertTrue($form->isPresent(),'form is present');
        $this->assertFalse($form->isValid(),'form is NOT valid');
    }

    // @todo more thorough testing needed here...

}
