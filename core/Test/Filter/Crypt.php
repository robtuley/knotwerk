<?php
/**
 * Unit test cases for the T_Filter_Crypt class.
 *
 * This file contains the definition of a series of unit tests that ensures
 * that the T_Filter_Crypt class is working correctly.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_Crypt test cases.
 *
 * This class defines a series of unit tests for the T_Filter_Crypt class.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Filter_Crypt extends T_Unit_Case
{

    /**
     * Test data.
     *
     * @var array
     */
    protected $data;

    /**
     * Setup common test data.
     */
    function setUp()
    {
        $this->data = array ( 'bool'   => true,
                              'string' => 'some secret string',
                              'int'    => 3,
                              'float'  => 3.142,
                              'array'  => array(1,4,5,6) );
    }

    /**
     * Test encrypt then decrypt.
     *
     * This function asserts that data can be encrypted and then decrypted using
     * either the same object, or different object instances with the same
     * password.
     */
    protected function assertEncryptDecrypt($algorithm,$mode)
    {
        $passwd = 'hgsfdyuwl';

        // test with persistent object
        $crypt = new T_Filter_Crypt($passwd,$algorithm,$mode);
        foreach ($this->data as $key=>$value) {
            $encrypted = $crypt->transform($value);
            // make safe for testing
            $this->assertNotEquals(md5(serialize($encrypted)),
                                   md5(serialize($value))      );
            $decrypted = $crypt->reverse($encrypted);
            $this->assertSame($decrypted,$value);
        }

        // test with non-peristent object
        foreach ($this->data as $key=>$value) {
            $crypt = new T_Filter_Crypt($passwd,$algorithm,$mode);
            $encrypted = $crypt->transform($value);
            // make safe for testing
            $this->assertNotEquals(md5(serialize($encrypted)),
                                   md5(serialize($value))      );
            unset($crypt);
            $crypt = new T_Filter_Crypt($passwd,$algorithm,$mode);
            $decrypted = $crypt->reverse($encrypted);
            $this->assertSame($decrypted,$value);
            unset($crypt);
        }
    }

    /**
     * Tests Rijndael encryption.
     *
     * Checks the Rijndael encryption algorithm under both ecb and
     * ofb modes. The ecb mode doesn't require sucessive IV values to be the
     * same, but the ofb mode does.
     */
    function testRijndaelEncryptDecrypt()
    {
        $this->assertEncryptDecrypt('rijndael-256','ecb');
        $this->assertEncryptDecrypt('rijndael-256','ofb');
    }

    /**
     * Tests Blowfish encryption.
     *
     * Checks blowfish encryption algorithm under two different modes (ecb and
     * ofb)
     */
    function testBlowfishEncryptDecrypt()
    {
        $this->assertEncryptDecrypt('blowfish','ecb');
        $this->assertEncryptDecrypt('blowfish','ofb');
    }

    /**
     * Run mcrypt module self-test.
     */
    function testSelfModuleMcrypt()
    {
        $this->assertTrue(mcrypt_module_self_test(MCRYPT_RIJNDAEL_256));
        $this->assertTrue(mcrypt_module_self_test(MCRYPT_BLOWFISH));
    }

    /**
     * Test that setting encryption salt has an effect.
     */
    function testSetSaltHasAnAffectOnEncryptedValues()
    {
        T_Filter_Crypt::setSalt('first');
        $crypt1 = new T_Filter_Crypt('password','rijndael-256','ecb');
        $encrypted1 = $crypt1->transform('value');
        T_Filter_Crypt::setSalt('second');
        $crypt2 = new T_Filter_Crypt('password','rijndael-256','ecb');
        $encrypted2 = $crypt2->transform('value');
        $this->assertNotSame($encrypted1,$encrypted2);
    }

    /**
     * Dismantle test setup.
     *
     * This function unsets the test data array and object ready for a new test.
     */
    function tearDown()
    {
        unset($this->data);
    }

}