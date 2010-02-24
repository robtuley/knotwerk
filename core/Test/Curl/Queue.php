<?php
class T_Test_Curl_Queue extends T_Test_Curl_Harness
{

    protected function getRequests()
    {
        return array( new T_Curl_Get('http://knotwerk.com'),
                      new T_Curl_Get('http://knotwerk.com/download'),
                      new T_Curl_Get('http://knotwerk.com/ref') );
    }

    function testCanExecuteSingleRequestInNonBlockingQueue()
    {
        $r = _first($this->getRequests());
        $q = new T_Curl_Queue();
        $this->assertSame($q,$q->queue($r));
        $this->assertFalse(strlen($r->getBody())>0);
        $this->assertFalse($q->ping($r));
          // ^ non-blocking so request just started to execute
        $this->assertSame($q,$q->waitFor($r));
          // ^ blocks until curl request completed
        $this->assertTrue($q->ping($r));
        $this->assertTrue(strlen($r->getBody())>0);
    }

    function testCanExecuteMultipleRequestInParallelInQueue()
    {
        $rs = $this->getRequests();
        $q = new T_Curl_Queue();
        foreach ($rs as $r) $q->queue($r);
        $this->assertSame($q,$q->waitForAll());
        foreach ($rs as $r) {
            $this->assertTrue(strlen($r->getBody())>0);
        }
    }

    function testCanRetrieveSingleRequestWhenReadyBeforeOthersHaveFinished()
    {
        $rs = $this->getRequests();
        $q = new T_Curl_Queue();
        foreach ($rs as $r) $q->queue($r);
        foreach ($rs as $r) {
            $q->waitFor($r);
            $this->assertTrue(strlen($r->getBody())>0);
        }
    }

    function testCanLimitQueueToAMaxConnectionThreshold()
    {
        $rs = $this->getRequests();
        $q = new T_Curl_Queue(count($rs)-1);
        foreach ($rs as $r) $q->queue($r);
        foreach ($rs as $r) {
            $q->waitFor($r);
            $this->assertTrue(strlen($r->getBody())>0);
        }
    }

}