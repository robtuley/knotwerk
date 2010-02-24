<?php
/**
 * Contains the T_Filter_Crypt class.
 *
 * This file defines the T_Filter_Crypt class that is used to encrypt and decrypt
 * data using the mcrypt extension.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encryption and Decryption.
 *
 * This class is used to encrypt and decrypt data using the mcrypt PHP
 * extension. The class itself requires the libmcrypt library version 2.5.6 or
 * greater, and the mcrypt PHP extension installed.
 *
 * Example:
 * <code>
 * $cipher = new T_Filter_Crypt('secret key');
 * $data  = 'some sensitive data';
 * // encrypt data
 * $encrypted = $cipher->encrypt($data);
 * // decrypt data
 * $data = $cipher->decrypt($encrypted);
 * </code>
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Filter_Crypt implements T_Filter_Reversable
{

    /**
     * Encryption module handle.
     *
     * To encrypt and decrypt with the mcrypt extension, need to open an
     * encryption module. This attribute stores the reference to this
     * module.
     *
     * @var resource
     */
    protected $td;

    /**
     * Encryption Key.
     *
     * The key is generated from a password passed into the constructor.
     *
     * @var string
     */
    protected $key;

    /**
     * The salt to use for encryption.
     *
     * @var string
     */
    private static $salt = 'ttuiygoo87';

    /**
     * Set the encryption salt to a new value.
     *
     * @param string $salt
     */
    public static function setSalt($salt)
    {
        self::$salt = (string) $salt;
    }

    /**
     * Open mcrypt module.
     *
     * The constructor function opens the encryption module.
     *
     * @param string $passwd  secret pass phrase required to decrypt/encrypt
     * @param string $algorithm  encryption algorithm to use
     * @param string $mode  encryption mode to use
     */
    function __construct($passwd,$algorithm='rijndael-256',$mode='ecb')
    {
        // open encryption module
        $this->td = mcrypt_module_open($algorithm,'',$mode,'');
        if (!$this->td) {
            $msg = "No support for $algorithm under mode $mode.";
            throw new BadFunctionCallException($msg);
        }
        // generate key
        $len = mcrypt_enc_get_key_size($this->td);
        $this->key = substr(sha1($passwd.self::$salt,true),0,$len);
    }

    /**
     * Encrypt data.
     *
     * This function takes any data and encrypts it. The data is serialized
     * before encryption so any type is permitted, and the data will maintain
     * this type.
     *
     * @param mixed $data  data to encrypt
     * @return string  encrypted data string
     */
    function transform($data)
    {
        $iv = $this->createIv();
        mcrypt_generic_init($this->td,$this->key,$iv);
        // IV is appended to the beginning of the encrypted string
        $encrypt = $iv.mcrypt_generic($this->td,serialize($data));
        mcrypt_generic_deinit($this->td);
        return base64_encode($encrypt);
    }

    /**
     * Decrypt data.
     *
     * This function takes an encrypted string and unencrypts it using the
     * passwd.
     *
     * @param string $encrypted  encrpted data string
     * @return mixed  unencrypted data
     */
    function reverse($encrypted)
    {
        $encrypted = base64_decode($encrypted);
        $iv_len = mcrypt_enc_get_iv_size($this->td);
        // IV is retrieved from the front of the encrypted string
        $iv = substr($encrypted,0,$iv_len);
        mcrypt_generic_init($this->td,$this->key,$iv);
        $plain = mb_trim(mdecrypt_generic($this->td,substr($encrypted,$iv_len)));
        mcrypt_generic_deinit($this->td);
        return unserialize($plain);
    }

    /**
     * Create a new initialisation vector.
     *
     * This function creates a new initialisation vector (IV) string of the
     * appropriate length.
     *
     * @return string  initialisation vector string
     */
    protected function createIv()
    {
        if (strtoupper(substr(php_uname('s'),0,3))==='WIN') {
            $seed = MCRYPT_RAND;  // only seed available on windows
        } else {
            $seed = MCRYPT_DEV_URANDOM;
        }
        return mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td),$seed);
    }

    /**
     * Close mcrypt module.
     *
     * On object destruct, we need to close the mcrypt module.
     */
    function __destruct()
    {
        mcrypt_module_close($this->td);
    }

}