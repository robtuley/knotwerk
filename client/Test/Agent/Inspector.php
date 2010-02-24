<?php
class T_Test_Agent_Inspector extends T_Unit_Case
{

    function getInspector()
    {
        return new T_Agent_Inspector;
    }

    function testAddingParsersHasAFluentInterface()
    {
        $p = new T_Agent_Mock_Parser('ans');
        $agent = $this->getInspector();
        $this->assertSame($agent,$agent->addParser($p));
    }

    // test that call to parser exposed function returns correct
    // results

    // single parser

    function testCallNonExistantParserMethodOrNoParsersReturnsNull()
    {
        $agent = $this->getInspector();
        $this->assertTrue(is_null($agent->notAMethod()));
        $agent->addParser(new T_Agent_Mock_Parser('ans'));
        $this->assertTrue(is_null($agent->notAMethod()));
    }

    function testAnswerReturnedWhenSingleParserGivesResult()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser('ans'));
        $this->assertSame('ans',$agent->exampleMethod());
    }

    function testNoAnswerReturnedWhenSingleParserReturnsNoResult()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser(null));
        $this->assertTrue(is_null($agent->exampleMethod()));
    }

    function testCanCallThroughToParserWithAnArgument()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser(null));
        $this->assertSame('arg',$agent->reflectArg('arg'));
    }

    // multiple parsers

    function testThatASingleAnswerFromMultipleParsersIsReturned()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser(null));
        $agent->addParser(new T_Agent_Mock_Parser('ans'));
        $this->assertSame('ans',$agent->exampleMethod());
    }

    function testMultipleAnswersHighestConfidenceIsUsed()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser('ans0',
                            T_Agent_Answer::TENTATIVE));
        $agent->addParser(new T_Agent_Mock_Parser('ans1',
                            T_Agent_Answer::DEFINITE));
        $agent->addParser(new T_Agent_Mock_Parser('ans2',
                            T_Agent_Answer::LIKELY));
        $this->assertSame('ans1',$agent->exampleMethod());
    }

    function testWithAnswersOnSameLevelValueVoteWins()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser('ans0',
                            T_Agent_Answer::LIKELY));
        $agent->addParser(new T_Agent_Mock_Parser('ans1',
                            T_Agent_Answer::LIKELY));
        $agent->addParser(new T_Agent_Mock_Parser('ans1',
                            T_Agent_Answer::LIKELY));
        $agent->addParser(new T_Agent_Mock_Parser('ans2',
                            T_Agent_Answer::TENTATIVE));
        $this->assertSame('ans1',$agent->exampleMethod());
    }

    function testTwoIdenticalValueAnswerOnlyOneIsReturned()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser('ans1'));
        $agent->addParser(new T_Agent_Mock_Parser('ans0'));
        $r = $agent->exampleMethod();
        $this->assertTrue($r==='ans1' || $r==='ans0');
    }

    // test that atLeast() preceding the fluent call drops any
    // answers below the threshold

    function testDropsAnswerBelowThreshold()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser('ans',
                            T_Agent_Answer::TENTATIVE));
        $r = $agent->atLeast(T_Agent_Answer::LIKELY)
                   ->exampleMethod();
        $this->assertTrue(is_null($r));
        $this->assertSame('ans',$agent->exampleMethod());
    }

    function testKeepsAnswerEqualsOrAboveMinLevel()
    {
        $agent = $this->getInspector();
        $agent->addParser(new T_Agent_Mock_Parser('ans',
                            T_Agent_Answer::LIKELY));
        $r = $agent->atLeast(T_Agent_Answer::LIKELY)
                   ->exampleMethod();
        $this->assertSame('ans',$r);
        $r = $agent->atLeast(T_Agent_Answer::TENTATIVE)
                   ->exampleMethod();
        $this->assertSame('ans',$r);
    }

}

// mock parser

class T_Agent_Mock_Parser implements T_Agent_Parser
{

    protected $ans;
    protected $level;

    function __construct($ans,$level=T_Agent_Answer::LIKELY)
    {
        $this->ans = $ans;
        $this->level = $level;
    }

    function exampleMethod()
    {
        if (is_null($this->ans)) {
            return null;
        } else {
            return new T_Agent_Answer($this->ans,$this->level);
        }
    }

    function reflectArg($arg)
    {
        return new T_Agent_Answer($arg,$this->level);
    }

}