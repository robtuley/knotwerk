<?php
/**
 * Defines the T_Session_NativeDriver class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Inbuilt session driver (PHP engine, usually file based).
 *
 * @package core
 */
class T_Session_NativeDriver implements T_Session_Driver
{

    /**
     * Session filters.
     *
     * @var T_Filter_Reversable[]
     */
    protected $filters = array();

    /**
     * Configures the in-built session.
     *
     * A value of 'private' for the cache limiter prevents 'page has expired'
     * warnings and enables client caching of session pages. However, it should
     * not be used with POST forms or other content that should not be cached
     * by the client. The default is to disallow all caching.
     *
     * @see http://shiflett.org/articles/how-to-avoid-page-has-expired-warnings
     * @param string $name session name
     * @param string $dir  directory to save session files in
     * @param bool $https_only  maintain session for https requests only
     * @param T_Url $url  root url for session (if on shared host)
     * @param string $cache_limiter  caching type ('nocache' by default)
     * @param int  $cache_expire  cache expiry (mins)
     */
    function __construct($name='sid',$dir=null,$https_only=false,T_Url $url=null,
                         $cache_limiter='nocache',$cache_expire=30)
    {
        // use cookies only
        ini_set('session.use_only_cookies',1);
        ini_set('session.cookie_httponly',1);
        // set session name
        session_name($name);
        // setup cookie parameters
        if ($url) {
            $path = '/';
            if (count($url->getPath())>0) $path .= implode('/',$url->getPath()).'/';
            $domain = $url->getHost();
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
        } else {
            $path = '/';
            $domain = null;
        }
        session_set_cookie_params(null,$path,$domain,$https_only);
        // set save path
        if ($dir) {
            session_save_path($dir);
        } else {
            $default = new T_File_Dir(T_CACHE_DIR.'session');
            session_save_path($default->__toString());
             // it is not safe on shared hosts to store session files in shared
             // temporary dirs. Therefore by default store session files in
             // cache directory.
        }
        // configure caching
        session_cache_limiter($cache_limiter);
        session_cache_expire($cache_expire);
    }

    /**
     * Retrieves data.
     *
     * @return mixed  data
     */
    function get()
    {
        if (!session_id()) session_start();
        if (isset($_SESSION['__data__'])) {
            $data = $_SESSION['__data__'];
        } else {
            $data = array();
        }
        foreach ($this->filters as $f) {
            $data = $f->reverse($data);
        }
        return $data;
    }


    /**
     * Saves data.
     *
     * @param mixed $data  data
     * @return T_Session_Driver  fluent interface
     */
    function save($data)
    {
        foreach ($this->filters as $f) {
            $data = _transform($data,$f);
        }
        $_SESSION['__data__'] = $data;
        session_write_close();
        return $this;
    }

    /**
     * Attach a driver filter.
     *
     * @param T_Filter_Reversable $filter
     * @return T_Session_Driver  fluent interface
     */
    function attachFilter(T_Filter_Reversable $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Regenerates session driver.
     *
     * @return T_Session_Driver  fluent interface
     */
    function regenerate()
    {
        session_regenerate_id(true);
        return $this;
    }

    /**
     * Destroys session.
     *
     * @return T_Session_Driver  fluent interface
     */
    function destroy()
    {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
        return $this;
    }

}
