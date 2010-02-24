<?php
/**
 * Defines the T_Test_Cage_CookieStub class.
 *
 * @package coreTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates Cookie For Unit Testing.
 *
 * This stub modifies the cookie set method so it doesn't try to set cookies
 * during unit testing.
 *
 * @package coreTests
 * @license http://knotwerk.com/licence MIT
 */
class T_Test_Cage_CookieStub extends T_Cage_Cookie implements T_Test_Stub
{

    /**
     * Number of Cookies set.
     *
     * @var int
     */
    protected $ncookies = 0;

    /**
     * Arguments sent to setcookie function.
     *
     * @var array
     */
    protected $args = array();

    /**
     * Simulate setting a cookie.
     *
     * @param string $name  cookie name
     * @param string $value  cookie value
     * @param int $expires  expiry time (UNIX time)
     * @param string $path  path on which the cookie is available
     * @param string $domain  domain on which cookie is available
     * @param bool $secure  whether to only send the cookie over https
     */
    function doCookieSet($name, $value, $expires = null, $path = null,
                                                $domain = null, $secure = null)
    {
        $this->ncookies++;
        $this->args[] = array( 'name'    => $name,
                               'value'   => $value,
                               'expires' => $expires,
                               'path'    => $path,
                               'domain'  => $domain,
                               'secure'  => $secure);

    }

    /**
     * Gets the number of cookies that have been set.
     *
     * @return int
     */
    function getNumberCookiesSet()
    {
        return $this->ncookies;
    }

    /**
     * Gets an array of setcookie arguments on the (n-1)th call.
     *
     * @param int $index  (n-1)th call to get args from
     * @return array  setcookie arguments
     */
    function getSetCookieArgArray($index)
    {
        return $this->args[$index];
    }

    /**
     * Reset counter and args.
     */
    function reset()
    {
        $this->ncookies = 0;
        $this->args = array();
    }

}