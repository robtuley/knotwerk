<?php
class T_Test_Filter_Suffix implements T_Filter_Reversable
{

    protected $suffix;

    function __construct($suffix='Tested')
    {
        $this->suffix = $suffix;
    }

    function transform($value)
    {
        return $value.$this->suffix;
    }

    function reverse($value)
    {
        return substr($value,0,-1*strlen($this->suffix));
    }

}
