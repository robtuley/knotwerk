<?php
class T_Test_Filter_RepeatableHash extends T_Unit_Case
{

    function testFilterCreatesHexHash()
    {
        $key = new T_Filter_RepeatableHash();
        $hash = $key->transform('some value');
        $this->assertTrue(strlen($hash)>0);
        $this->assertTrue(ctype_xdigit($hash));
    }

    function testHashIsRepeatableForSameData()
    {
        $key = new T_Filter_RepeatableHash();
        $this->assertSame($key->transform('data'),$key->transform('data'));
    }

    function testHashIsRepeatableBetweenDifferentInstances()
    {
        $key1 = new T_Filter_RepeatableHash();
        $key2 = new T_Filter_RepeatableHash();
        $this->assertSame($key1->transform('data'),
                          $key2->transform('data'));

    }

    function testHashIsDifferentForDifferentData()
    {
        $key = new T_Filter_RepeatableHash();
        $this->assertNotEquals($key->transform('data1'),
                               $key->transform('data2'));
    }

    function testCanHashArrays()
    {
        $key = new T_Filter_RepeatableHash();
        $hash = $key->transform(array(1,2,3));
        $this->assertTrue(strlen($hash)>0);
        $this->assertTrue(ctype_xdigit($hash));
        $this->assertSame($hash,$key->transform(array(1,2,3)));
        $this->assertNotEquals($hash,$key->transform(array(1,3)));
    }

    function testCanHashObjects()
    {
        $key = new T_Filter_RepeatableHash();
        $hash = $key->transform(new T_Cage_Scalar('data'));
        $this->assertTrue(strlen($hash)>0);
        $this->assertTrue(ctype_xdigit($hash));
        $this->assertSame($hash,$key->transform(new T_Cage_Scalar('data')));
        $this->assertNotEquals($hash,$key->transform(new T_Cage_Scalar('diff')));
    }

}
