<?php
class T_Test_Filter_ToUrlQuery extends T_Test_Filter_SkeletonHarness
{

    function getFilter()
    {
        return new T_Filter_ToUrlQuery;
    }

    function testCanConvertEmptyArrayToString()
    {
        $this->assertSame('',$q=$this->getFilter()->transform(array()));
    }

    function testCanConvertSingleElement()
    {
        $this->assertSame('name=val',
                          $this->getFilter()->transform(array('name'=>'val')));
    }

    function testCanConvertMultipleArrayValuesToString()
    {
        $data = array('one'=>1,'two'=>2,'three'=>'3');
        $this->assertSame('one=1&two=2&three=3',$this->getFilter()->transform($data));
    }

    function testCanStringAndReverseElements()
    {
        $try = array(
                    array(),
                    array('name'=>'val'),
                    array('one'=>1,'two'=>2,'three'=>'3'),
                    array("na'me"=>"va'lue"),  // test magic quotes
                    array('name'=>'val','nest'=>array('one','two','three')),
                    );
        $f = $this->getFilter();
        foreach ($try as $e) {
            $this->assertEquals($e,$f->reverse($f->transform($e)));
        }
    }

}