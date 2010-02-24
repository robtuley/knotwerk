<?php
/**
 * Contains the T_Random class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Random item factory.
 *
 * @package core
 */
class T_Random
{

    /**
     * Generates a random hexadecimal hash.
     *
     * @param int $len  length (default 32)
     * @return string  unique hex hash
     */
    function createHash($len=32)
    {
        $hash = '';
        while (strlen($hash)<$len) {
            $hash .= md5(uniqid(rand(),true));
        }
        return substr($hash,0,$len);
    }

    /**
     * Generates a random string from set a-z,0-9.
     *
     * @param int $len  length (default 32)
     * @return string  unique string salt
     */
    function createSalt($len=32)
    {
        $salt = '';
        while (strlen($salt)<$len) {
            $salt .= base_convert(md5(uniqid(rand(),true)),16,36);
        }
        return substr($salt,0,$len);
    }

}
