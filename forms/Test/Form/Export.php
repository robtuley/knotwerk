<?php
class T_Test_Form_Export extends T_Unit_Case
{

    function testDataIsEmptyArrayByDefault()
    {
        $export = new T_Form_Export;
        $this->assertSame(array(),$export->getData());
    }

    // @todo all the extra export tests

}