<?php
/**
 * Defines the T_Cage_Cookie class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates the $_COOKIE superglobal.
 *
 * This object is used to encapsulate the superglobal $_COOKIE. It enforces a
 * data cage round the values, and allows easy cookie setting and deleting.
 *
 * @package core
 */
class T_Cage_Cookie extends T_Cage_Array
{

    /**
     * Application root.
     *
     * @var T_Url
     */
    protected $root = false;

    /**
     * Set the root URL.
     *
     * @param T_Url $url
     * @return T_Cage_Cookie  fluent interface
     */
    function setRootUrl(T_Url $url)
    {
        $this->root = $url;
        return $this;
    }

    /**
     * Set a cookie.
     *
     * @param string $name  cookie name
     * @param string $value  cookie value
     * @param int $expires  expiry time (UNIX time)
     * @param string $path  path on which the cookie is available
     * @param string $domain  domain on which cookie is available
     * @param bool $secure  whether to only send the cookie over https
     */
    function set($name,$value,$expires=null,$path=null,$domain=null,$secure=null)
    {
        // if domain/path is default (null), and a server document root value is
        // available, then we use that as these parameters.
        if (is_null($path) && is_null($domain) && $this->root) {
            $path = '/';
            if (count($this->root->getPath())>0) {
                $path .= implode('/',$this->root->getPath()).'/';
            }
            $domain = $this->root->getHost();
            // remove any 'www.' prefix subdomain as not relevant, and take off
            // any port information.
            if (strncasecmp('www.',$domain,4)===0) $domain = substr($domain,4);
            if ( ($pos=strpos($domain,':'))!==false ) {
                $domain = substr($domain,0,$pos);
            }
            if (strpos($domain,'.')===false) {
                $domain = null;
                  // HTTP protocol doesn't allow setting top level domains like
                  // 'localhost' for security reasons
            } else {
                $domain = '.'.$domain; // prefix domain with dot to make sure it is
                                       // available on all sub-domains
            }
        } elseif (strlen($domain)>0 && strpos($domain,'.')===false) {
            $msg = "$domain is a TLD which HTTP protocol forbids in a cookie";
            throw new InvalidArgumentException($msg);
        }
        if ($expires>time() || !is_int($expires)) {
            $this->data[$name] = $value;
        } else { // deleting cookie
            unset($this->data[$name]);
            $value = null;
        }
        $this->doCookieSet($name,$value,$expires,$path,$domain,$secure);
        return $this;
    }

    /**
     * Actually set the cookie.
     *
     * This can be over-written in inheritance to test the class.
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     */
    protected function doCookieSet($name,$value,$expires=null,$path=null,
                                                      $domain=null,$secure=null)
    {
        setcookie($name,$value,$expires,$path,$domain,$secure);
    }

    /**
     * Delete cookie.
     *
     * @param mixed $offset  cookie name
     */
    function delete($name)
    {
        $this->set($name,'',time()-42000);  // delete cookie
        return $this;
    }

}
