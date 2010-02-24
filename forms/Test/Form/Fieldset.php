<?php
class T_Test_Form_Fieldset extends T_Test_Form_Group
{

    function getInputCollection($alias,$label)
    {
        return new T_Form_Fieldset($alias,$label);
    }

}