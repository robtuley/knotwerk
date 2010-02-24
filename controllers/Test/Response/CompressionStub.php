<?php
class T_Test_Response_CompressionStub
      extends T_Response_Compression implements T_Test_Stub
{

    protected $zlib = false;

    protected function isZlibCompressed()
    {
        return $this->zlib;
    }

    function setZlibCompressed($onoff)
    {
        $this->zlib = $onoff;
        return $this;
    }

}
