<?php
class T_Test_Form_Step extends T_Test_Form_Group
{

    function getInputCollection($alias,$label)
    {
        return new T_Form_Step($alias,$label);
    }

}