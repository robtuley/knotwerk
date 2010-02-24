<?php
class T_Test_Form_Button extends T_Unit_Case
{

    function getButton($alias,$label)
    {
        return new T_Form_Button($alias,$label);
    }

    // tests

    function testAliasSetThroughConstructor()
    {
        $input = $this->getButton('myalias','mylabel');
        $this->assertSame($input->getAlias(),'myalias');
    }

    function testLabelSetThroughConstructor()
    {
        $input = $this->getButton('myalias','mylabel');
        $this->assertSame($input->getLabel(),'mylabel');
    }

    function testFilterCanBePassedToGetLabelMethod()
    {
        $input = $this->getButton('myalias','mylabel');
        $f =  new T_Test_Filter_Suffix();
        $this->assertSame($input->getLabel($f),$f->transform('mylabel'));
    }

    function testAliasCastToStringInConstructor()
    {
        $input = $this->getButton(123,'mylabel');
        $this->assertSame($input->getAlias(),'123');
    }

    function testLabelCastToStringInConstructor()
    {
        $input = $this->getButton('myalias',123);
        $this->assertSame($input->getLabel(),'123');
    }

    function testConstructorFailureWithZeroLengthAlias()
    {
        try {
            $input = $this->getButton(null,'mylabel');
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testConstructorFailureWithZeroLengthLabel()
    {
        try {
            $input = $this->getButton('myalias',null);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFieldnameIsAliasByDefault()
    {
        $input = $this->getButton('myalias','mylabel');
        $this->assertSame('myalias',$input->getFieldname());
    }

    function testSaltedFieldnameIsHashed()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertNotEquals($input->getFieldname(),$input->getAlias());
        $this->assertTrue(ctype_xdigit($input->getFieldname()));
    }

    function testRepeatedCallsToSaltedGetFieldnameProduceSameResults()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($input->getFieldname(),$input->getFieldname());
    }

    function testDifferentSaltProducesDifferentFieldname()
    {
        $input1 = $this->getButton('myalias','mylabel');
        $input2 = $this->getButton('myalias','mylabel');
        $input1->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $input2->setFieldnameSalt('salt2',new T_Filter_RepeatableHash());
        $this->assertNotSame($input1->getFieldname(),$input2->getFieldname());
    }

    function testSetFieldnameSaltHasFluentInterface()
    {
        $input = $this->getButton('myalias','mylabel');
        $test = $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $this->assertSame($test,$input);
    }

    function testSaltCanBeChanged()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->setFieldnameSalt('salt1',new T_Filter_RepeatableHash());
        $first = $input->getFieldname();
        $input->setFieldnameSalt('salt2',new T_Filter_RepeatableHash());
        $second = $input->getFieldname();
        $this->assertNotEquals($first,$second);
    }

    function testNotPresentByDefault()
    {
        $input = $this->getButton('myalias','mylabel');
        $this->assertFalse($input->isPresent());
    }

    function testNotPresentIfNotSetInSubmission()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->validate(new T_Cage_Array(array()));
        $this->assertFalse($input->isPresent());
    }

    function testIsPresentIfIsSetInSubmission()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->validate(new T_Cage_Array(array('myalias'=>'value')));
        $this->assertTrue($input->isPresent());
    }

    function testIsPresentIfIsSetAsZeroLengthStringInSubmission()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->validate(new T_Cage_Array(array('myalias'=>'')));
        $this->assertTrue($input->isPresent());
    }

    function testUsesHashedFieldnameToAssessIfPresent()
    {
        $input = $this->getButton('myalias','mylabel');
        $input->setFieldnameSalt('mysalt',new T_Filter_RepeatableHash());
        $source = new T_Cage_Array(array($input->getFieldname()=>''));
        $input->validate($source);
        $this->assertTrue($input->isPresent());
    }

    function testIsNotSubmittedIfNotPresent()
    {
        $input = $this->getButton('myalias','mylabel');
        $this->assertFalse($input->isSubmitted(new T_Cage_Array(array())));
    }

    function testIsSubmittedIfIsPresentAsNonZerLengthValue()
    {
        $input = $this->getButton('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>'value'));
        $this->assertTrue($input->isSubmitted($source));
    }

    function testIsSubmittedIfIsPresentAsNullValue()
    {
        $input = $this->getButton('myalias','mylabel');
        $source = new T_Cage_Array(array('myalias'=>null));
        $this->assertTrue($input->isSubmitted($source));
    }

}
