<?php
/**
 * Unit test cases for the T_Validate_Confirm class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Confirm unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_Confirm extends T_Test_Filter_SkeletonHarness
{

    function testFilterFailsIfMasterFieldIsNotAvailable()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $test->addChild(new T_Test_Form_ElementStub('master','label'));
        try {
            $filter->transform($test);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFilterFailsIfConfirmFieldIsNotAvailable()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $test->addChild(new T_Test_Form_ElementStub('slave','label'));
        try {
            $filter->transform($test);
            $this->fail();
        } catch (InvalidArgumentException $e) { }
    }

    function testFilterDoesNoActionIfMasterAlreadyHasError()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $master->attachFilter(new T_Test_Filter_Failure());
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array('master'=>'a','slave'=>'c')));
        $filter->transform($test);
        $this->assertTrue($slave->isValid());
        $this->assertFalse($master->isValid());
    }

    function testFilterDoesNoActionIfSlaveAlreadyHasError()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $slave->attachFilter(new T_Test_Filter_Failure());
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array('master'=>'a','slave'=>'c')));
        $filter->transform($test);
        $this->assertFalse($slave->isValid());
        $this->assertTrue($master->isValid());
    }

    function testFilterDoesNoActionIfMasterAndSlaveNotPresentButValid()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $master->setOptional();
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $slave->setOptional();
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array()));
        $filter->transform($test);
        $this->assertTrue($test->isValid());
    }

    function testFilterDoesNoActionIfMasterAndSlaveMatch()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array('master'=>'a','slave'=>'a')));
        $filter->transform($test);
        $this->assertTrue($test->isValid());
    }

    function testFilterAddsErrorToSlaveIfDoesNotMatchMaster()
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array('master'=>'a','slave'=>'b')));
        $filter->transform($test);
        $this->assertFalse($slave->isValid());
        $this->assertTrue($master->isValid());
        $this->assertTrue($slave->getError() instanceof T_Form_Error);
    }

    function testFilterComparesInALooseWay() // necessary for comparison of objects
    {
        $filter = new T_Validate_Confirm('master','slave');
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array('master'=>1,'slave'=>'1')));
        $filter->transform($test);
        $this->assertTrue($test->isValid());
    }

    function testPipePriorFilter()
    {
        $filter = new T_Validate_Confirm('master','slave',new T_Test_Filter_Failure());
        $test = new T_Form_Fieldset('container','label');
        $master = new T_Test_Form_ElementStub('master','label');
        $test->addChild($master);
        $slave = new T_Test_Form_ElementStub('slave','label');
        $test->addChild($slave);
        $test->validate(new T_Cage_Array(array('master'=>'a','slave'=>'a')));
        try {
            $filter->transform($test);
            $this->fail();
        } catch (T_Exception_Filter $e) { }
    }

}