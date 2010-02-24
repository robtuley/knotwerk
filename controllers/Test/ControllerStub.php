<?php
class T_Test_ControllerStub extends T_Controller implements T_Test_Stub
{

    protected $is_executed = false;
    protected $find_next = null;
    protected $delegated = '';

    function isExecuted()
    {
        return $this->is_executed;
    }

    function findNext($response)
    {
        if (!is_null($this->find_next)) {
            return $this->find_next;
        } else {
            return parent::findNext($response);
        }
    }

    function setFindNext($value)
    {
        $this->find_next = $value;
    }

    function isDelegatedTo()
    {
        return $this->delegated;
    }

    function GET($response)
    {
        $this->is_executed = true;
        $this->delegated = 'GET';
    }

    protected function mapToClassname($name)
    {
        if (strcmp($name,'false')===0) {
            return false;  // useful to test failure
        }
        return $name;
    }

}
