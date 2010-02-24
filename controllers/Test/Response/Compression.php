<?php
class T_Test_Response_Compression extends T_Unit_Case
{

    protected $response;

    function setup()
    {
        $this->response = new T_Test_ResponseStub();
    }

    function getEnvironment($server=null)
    {
        $input = array();
        if ($server) $input['SERVER'] = new T_Cage_Array($server);
        return new T_Test_EnvironmentStub($input);
    }

    function getFilter($env=null)
    {
        if (!$env) $env = $this->getEnvironment();
        $f = new T_Test_Response_CompressionStub($env);
        $f->setZlibCompressed(false);
        return $f;
    }

    // tests

    function testStartStopFilterNoBufferWithZlibOn()
    {
        $f = $this->getFilter()->setZlibCompressed(true);
        $blevel = ob_get_level();
        $f->preFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
        $f->postFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
    }

    function testStartAbortFilterNoBufferWithZlibOn()
    {
        $f = $this->getFilter()->setZlibCompressed(true);
        $blevel = ob_get_level();
        $f->preFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
        $f->abortFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
    }

    function testStartStopFilterIsBufferedWithZlibOff()
    {
        $f = $this->getFilter();
        $blevel = ob_get_level();
        $f->preFilter($this->response);
        $this->assertSame($blevel+1,ob_get_level());
        $f->postFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
    }

    function testStartAbortFilterIsBufferedWithZlibOff()
    {
        $f = $this->getFilter();
        $blevel = ob_get_level();
        $f->preFilter($this->response);
        $this->assertSame($blevel+1,ob_get_level());
        $f->abortFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
    }

    function testGzipForceWithZlibOnAndClientNotAcceptGzip()
    {
        $f = $this->getFilter();
        ob_start();
        $f->setZlibCompressed(true);
        $blevel = ob_get_level();
        $f->preFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
        /* force gzip now starts buffering
           and sets gzip header. */
        $f->forceGzipOutput();
        $this->assertSame($blevel+1,ob_get_level());
        $headers = $this->response->getHeaders();
        $this->assertSame($headers,array('Content-Encoding'=>'gzip'));
        echo 'test'; // must be *after* force call.
        $f->postFilter($this->response);
        /* post filter stops buffering, and gzips output */
        $this->assertSame($blevel,ob_get_level());
        $content = $this->gzdecode(ob_get_clean());
        $this->assertSame($content,'test');
    }

    function testGzipForceWithZlibOnAndClientDoesAcceptGzip()
    {
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip';
        $f = $this->getFilter($this->getEnvironment($server));
        ob_start();
        $f->setZlibCompressed(true);
        $blevel = ob_get_level();
        $f->preFilter($this->response);
        $this->assertSame($blevel,ob_get_level());
        /* force gzip now does nothing */
        $f->forceGzipOutput();
        $headers = $this->response->getHeaders();
        $this->assertSame($headers,array());
        $this->assertSame($blevel,ob_get_level());
        echo 'test';
        $f->postFilter($this->response);
        /* post filter does nothing */
        $this->assertSame($blevel,ob_get_level());
        $this->assertSame(ob_get_clean(),'test');
    }

    function testStartAbortDiscardsBufferContent()
    {
        $f = $this->getFilter();
        ob_start();
        $f->preFilter($this->response);
        echo 'test';
        $f->abortFilter($this->response);
        $this->assertSame('',ob_get_clean());
    }

    function testNoGzipWhenEncodingHeaderNotSet()
    {
        $f = $this->getFilter();
        ob_start();
        $f->preFilter($this->response);
        echo 'test';
        $f->postFilter($this->response);
        $this->assertSame(ob_get_clean(),'test');
    }

    function testNoGzipWhenEncodingHeaderNotGzip()
    {
        $server['HTTP_ACCEPT_ENCODING'] = 'not an encoding';
        $f = $this->getFilter($this->getEnvironment($server));
        ob_start();
        $f->preFilter($this->response);
        echo 'test';
        $f->postFilter($this->response);
        $this->assertSame(ob_get_clean(),'test');
    }

    function testGzippedWhenEncodingHeaderIsGzip()
    {
        $server['HTTP_ACCEPT_ENCODING'] = 'other,gzip';
        $f = $this->getFilter($this->getEnvironment($server));
        ob_start();
        $f->preFilter($this->response);
        echo 'test';
        $f->postFilter($this->response);
        $content = $this->gzdecode(ob_get_clean());
        $this->assertSame($content,'test');
    }

    function testGzipHeaderSetWithGzippedContent()
    {
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip';
        $f = $this->getFilter($this->getEnvironment($server));
        ob_start();
        $f->preFilter($this->response);
        $headers = $this->response->getHeaders();
        echo 'test';
        $f->postFilter($this->response);
        ob_get_clean();
        $this->assertSame($headers,array('Content-Encoding'=>'gzip'));
    }

    function testGzippedWhenForcedAndClientNotAcceptsGzip()
    {
        $f = $this->getFilter();
        ob_start();
        $f->preFilter($this->response);
        $f->forceGzipOutput();
        echo 'test'; // must be *after* force call.
        $f->postFilter($this->response);
        $content = $this->gzdecode(ob_get_clean());
        $this->assertSame($content,'test');
    }

    function testGzippedWhenForcedAndClientDoesAcceptGzip()
    {
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip';
        $f = $this->getFilter($this->getEnvironment($server));
        ob_start();
        $f->preFilter($this->response);
        $f->forceGzipOutput();
        echo 'test'; // must be *after* force call.
        $f->postFilter($this->response);
        $content = $this->gzdecode(ob_get_clean());
        $this->assertSame($content,'test');
    }

    function testHandlesZeroLengthContent()
    {
        $f = $this->getFilter();
        ob_start();
        $f->preFilter($this->response);
        $f->forceGzipOutput();
        $f->postFilter($this->response);
        $content = $this->gzdecode(ob_get_clean());
        $this->assertSame($content,'');
    }

    function testSetCompressionLevel()
    {
        $f = $this->getFilter();
        ob_start();
        $f->preFilter($this->response);
        $f->forceGzipOutput();
        echo 'test';
        $f->setCompressionLevel(9);
        $f->postFilter($this->response);
        $content = $this->gzdecode(ob_get_clean());
        $this->assertSame($content,'test');
    }

    function testSetCompressionLevelChangesZlibSettings()
    {
        $f = $this->getFilter();
        $f->setZlibCompressed(true);
        $f->preFilter($this->response);
        $f->setCompressionLevel(9);
        $f->postFilter($this->response);
        $this->assertEquals(9,ini_get('zlib.output_compression_level'));
    }

    function testNotStubDetectsCurrentZlibSettings()
    {
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip';
        ob_start();
        $f = new T_Response_Compression($this->getEnvironment($server));
        $f->preFilter($this->response);
        echo 'test';
        $f->postFilter($this->response);
        if (ini_get('zlib.output_compression')) {
            $this->assertSame(ob_end_clean(),'test');
        } else {
            $content = $this->gzdecode(ob_get_clean());
            $this->assertSame($content,'test');
        }
    }

    // gzdecode

    protected function gzdecode($data)
    {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
            throw new InvalidArgumentException('not gzipped data');
        }
        $method = ord(substr($data,2,1));  // Compression method
        $flags  = ord(substr($data,3,1));  // Flags
        if ($flags & 31 != $flags) {
            // Reserved bits are set -- NOT ALLOWED by RFC 1952
            throw new InvalidArgumentException('corrupt gzipped data');
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data,4,4));
        $mtime = $mtime[1];
        $xfl   = substr($data,8,1);
        $os    = substr($data,8,1);
        $headerlen = 10;
        $extralen  = 0;
        $extra = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $extralen = unpack("v",substr($data,8,2));
            $extralen = $extralen[1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $extra = substr($data,10,$extralen);
            $headerlen += 2 + $extralen;
        }

        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string file NAME data in header
            if ($len - $headerlen - 1 < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $filenamelen = strpos(substr($data,8+$extralen),chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $filename = substr($data,$headerlen,$filenamelen);
            $headerlen += $filenamelen + 1;
        }

        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerlen - 1 < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $comment = substr($data,$headerlen,$commentlen);
            $headerlen += $commentlen + 1;
        }

        $headercrc = "";
        if ($flags & 1) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerlen - 2 < 8) {
                throw new InvalidArgumentException('corrupt gzipped data');
            }
            $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data,$headerlen,2));
            $headercrc = $headercrc[1];
            if ($headercrc != $calccrc) {
                throw new InvalidArgumentException('bad CRC');
            }
            $headerlen += 2;
        }

        // GZIP FOOTER - These be negative due to PHP's limitations
        $datacrc = unpack("V",substr($data,-8,4));
        $datacrc = $datacrc[1];
        $isize = unpack("V",substr($data,-4));
        $isize = $isize[1];

        // Perform the decompression:
        $bodylen = $len-$headerlen-8;
        if ($bodylen < 1) {
            // This should never happen - IMPLEMENTATION BUG!
            throw new InvalidArgumentException('implementation bug');
        }
        $body = substr($data,$headerlen,$bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
                case 8:
                    // Currently the only supported compression method:
                    $data = gzinflate($body);
                    break;
                default:
                    throw new InvalidArgumentException('unknown compression');
           }
        } else {
            throw new InvalidArgumentException('zero byte content');
        }

        // Verifiy decompressed size and CRC32:
        // NOTE: This may fail with large data sizes depending on how
        //       PHP's integer limitations affect strlen() since $isize
        //       may be negative for large sizes.
        if ($isize != strlen($data) || crc32($data) != $datacrc) {
            // Bad format!  Length or CRC doesn't match!
            throw new InvalidArgumentException('corrupt gzipped data');
        }
        return $data;
    }

}
