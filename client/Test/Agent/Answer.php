<?php
class T_Test_Agent_Answer extends T_Unit_Case
{

    function testValueAndConfidenceSetInConstructor()
    {
        $ans = new T_Agent_Answer('ans',T_Agent_Answer::LIKELY);
        $this->assertSame('ans',$ans->getAnswer());
        $this->assertSame(T_Agent_Answer::LIKELY,$ans->getConfidence());
    }

    function testValueAndConfidenceCanBeFilteredOnRetrieval()
    {
        $ans = new T_Agent_Answer('ans',T_Agent_Answer::LIKELY);
        $f = new T_Filter_RepeatableHash;
        $this->assertSame($f->transform('ans'),$ans->getAnswer($f));
        $this->assertSame($f->transform(T_Agent_Answer::LIKELY),
                          $ans->getConfidence($f));
    }

}