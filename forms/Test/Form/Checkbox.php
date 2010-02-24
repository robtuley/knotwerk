<?php
class T_Test_Form_Checkbox extends T_Test_Form_ArrayElementHarness
{

    function getInputElement($alias,$label)
    {
        $input = new T_Form_Checkbox($alias,$label);
        $input->setOptions( array('value1'=>'Label 1',
                                  'value2'=>'Label 2',
                                  'value3'=>'Label 3') );
        return $input;
    }

    function getNewInstanceDefault()
    {
        return array();
    }

    function testDefaultSetAndGet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptions(array(10=>'label1',6=>'l','a'=>'l',3=>'l'));
        $input->setDefault(array('a',3));
        $this->assertSame(array('a',3),$input->getDefault());
    }

    function testFailureIfAttemptToSetNonArrayDefault()
    {
        $input = $this->getInputElement('myalias','mylabel');
        try {
            $input->setDefault('notanarray');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFailureIfAttemptToSetDefaultNotInOptions()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptions(array(1=>'label',4=>'label'));
        try {
            $input->setDefault(array(4,3,1));
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testDefaultCanBeFilteredDuringRetrieval()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptions(array('k1'=>'label1','k2'=>'label2'));
        $input->setDefault(array('k1','k2'));
        $f = new T_Test_Filter_ArrayPrefix();
        $this->assertSame(array('k1','k2'),$input->getDefault());
        $this->assertSame($f->transform(array('k1','k2')),$input->getDefault($f));
    }

    function testDefaultValueIsClearedWithInputNotSet()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $input->setOptional()
              ->setOptions(array(1=>'label',2=>'more label'))
              ->setDefault(array(1))
              ->validate(new T_Cage_Array(array()));
        $this->assertSame($input->getDefault(),array());
    }

    function testNoOptionsByDefault()
    {
        $input = new T_Form_Checkbox('myalias','mylabel');
        $this->assertSame($input->getOptions(),array());
    }

    function testSetOptionsMethodHasAFluentInterface()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $test = $input->setOptions(array(1,2,3));
        $this->assertSame($test,$input);
    }

    function testSetOptionsStoresOptionsLocally()
    {
        $input = $this->getInputElement('myalias','mylabel');
        $expect = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($expect);
        $this->assertSame($input->getOptions(),$expect);
    }

    function testSetOptionsWithNoPriorFilter()
    {
        $input = $this->getInputElement('alias','label');
        $options = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($options);
        $input->validate(new T_Cage_Array(array('alias'=>array('uk'))));
        $this->assertTrue($input->isValid());
        $input->validate(new T_Cage_Array(array('alias'=>array('us'))));
        $this->assertFalse($input->isValid());
    }

    function testSetOptionsWithExistingFilter()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Test_Filter_ArrayPrefix();
        $options = array('uk'=>'Britain','fr'=>'France');
        $input->setOptions($options);
        $input->attachFilter($f)->setOptions($options);
        /* test options filter */
        $input->validate(new T_Cage_Array(array('alias'=>array('us'))));
        $this->assertFalse($input->isValid());
        /* test other filter still exists */
        $input->validate(new T_Cage_Array(array('alias'=>array('uk'))));
        $this->assertTrue($input->isValid());
        $this->assertSame($input->getValue(),$f->transform(array('uk')));
    }

    function testChangeOptionsOnceAlreadySet()
    {
        $input = $this->getInputElement('alias','label');
        $f = new T_Test_Filter_ArrayPrefix();
        $options1 = array('uk'=>'Britain');
        $options2 = array('fr'=>'France');
        $input->attachFilter($f)
              ->setOptions($options1)
              ->setOptions($options2);
        $input->validate(new T_Cage_Array(array('alias'=>array('fr'))));
        $this->assertTrue($input->isValid());
        $input->validate(new T_Cage_Array(array('alias'=>array('uk'))));
        $this->assertFalse($input->isValid());
    }

    function testAttackWithOutOfRangeValuesResultsInNoDefault()
    {
        $input = $this->getInputElement('alias','label');
        $input->redisplayInvalid(true)
              ->redisplayValid(true)
              ->setOptions(array(2=>'first',5=>'second'))
              ->setDefault(array(2));
        $input->validate(new T_Cage_Array(array('alias'=>array(3))));
        $this->assertFalse($input->isValid());
        $this->assertSame($input->getDefault(),$this->getNewInstanceDefault());
    }

}
