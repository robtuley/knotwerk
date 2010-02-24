<?php
/**
 * Bootstraps the library environment.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

// ------------------------- CONSTANTS

if (!defined('T_ROOT_DIR')) {
    /**
     * Root library directory.
     *
     * @global string T_ROOT_DIR
     */
    define('T_ROOT_DIR',rtrim(rtrim(realpath(dirname(__FILE__)),'/'),'\\').
                        DIRECTORY_SEPARATOR);
}

if (!defined('T_CACHE_DIR')) {
    /**
     * Library cache directory.
     *
     * @global string T_CACHE_DIR
     */
    define('T_CACHE_DIR',T_ROOT_DIR.'.cache'.DIRECTORY_SEPARATOR);
}

if (!defined('T_PHP_EXT')) {
    /**
     * PHP file extension.
     *
     * @global string T_PHP_EXT
     */
    define('T_PHP_EXT',strrchr(__FILE__,'.'));
}

if (!defined('EOL')) {
    /**
     * Carriage Return.
     *
     * @global string EOL
     */
    define('EOL',"\n");
}

/**
 * Operating under windows.
 *
 * @global bool T_WINDOWS
 */
define('T_WINDOWS',strtoupper(substr(PHP_OS, 0, 3))==='WIN');

// ------------------------- UNICODE HANDLING

require_once T_ROOT_DIR.'unicode'.T_PHP_EXT;

// ------------------------- SHORTCUT FUNCTIONS

/**
 * Returns the last member of an array.
 *
 * This simply wraps the in-built end() function but is useful as
 * can be used directly on function return values as the array is
 * copied directly into the function so doesn't fail with reference errors.
 *
 * @param array $data
 * @return mixed   last member in array
 */
function _end(array $data)
{
    return end($data);
}

/**
 * Returns the first member of an array.
 *
 * This simply wraps the in-built reset() function but is useful as
 * can be used directly on function return values as the array is
 * copied directly into the function so doesn't fail with reference errors.
 *
 * @param array $data
 * @return mixed   last member in array
 */
function _first(array $data)
{
    return reset($data);
}

/**
 * Executes a filter on a value if there is one.
 *
 * @param mixed $value  value to filter
 * @param function $filter  filter to use to transform
 * @return mixed  filtered value
 */
function _transform($value,$filter)
{
    if ($filter instanceof T_Filter) {
        return $filter->transform($value);
    } elseif ($filter) {
        return call_user_func_array($filter,array($value));
    } else {
        return $value;
    }
}

// ------------------------- FACTORIES

/**
 * A factory to create an object.
 */
interface T_Factory
{

    /**
     * Creates a class instance.
     *
     * @param string $class classname
     * @param array $args  construct args
     * @return object  class instance
     */
    function like($class,array $args=array());

    /**
     * Configure factory.
     *
     * This method is used to configure the DI container. It can be used to
     * create singletons, and set the priority in which to create classes.
     *
     * <?php
     * $factory = new T_Factory_Di);
     * $factory->willUse(new T_Mysql_MasterOnly());        // setup singleton
     * $factory->willUse('T_User');                        // use T_User class
     * ?>
     *
     * By default, the DI container will register the input for it's own
     * classname, its parent classes and any interfaces it implements. If you
     * want to be a bit more surgical in what you are registering a class for,
     * you can include the optional second parameter, and specify the class you
     * want to replace.
     *
     * <?php
     * $factory->willUse('ReplacementController','OldController');
     * ?>
     *
     * @param string $class  classname
     * @param string $instead_of  to replace class
     */
    function willUse($class,$instead_of=null);

}

/**
 * Dependency injection factory.
 */
class T_Factory_Di implements T_Factory
{

    /**
     * Register self as the T_Factory population.
     */
    function __construct()
    {
        $this->willUse($this); // register self for factory population
    }

    /**
     * Stores class dependency lookup table.
     *
     * @var array
     */
    protected $lkup = array();

    /**
     * Cached reflection classes.
     *
     * @var ReflectionClass[]
     */
    protected $cache = array();

    /**
     * Configure DI container.
     *
     * @param string $class  classname
     * @param string $instead_of  additional alias
     */
    function willUse($class,$instead_of=null)
    {
        if (!is_null($instead_of)) {
            $this->lkup[strtolower($instead_of)] = $class;
        } else {
            // registers class for its:
            //  1. own name
            //  2. parent class names
            //  3. interface names
            if (is_object($class)) {
                if ($class instanceof T_Transparent) {
                    // in this case, the object is decorated, and we need to
                    // look *under* the decorators to get to the base class.
                    $classname = get_class($class->lookUnder());
                } else {
                    $classname = get_class($class);
                }
            } else {
                $classname = $class;
            }
            // register interfaces
            $reflect = $this->cache[strtolower($classname)] = new ReflectionClass($classname);
            foreach ($reflect->getInterfaces() as $interface) {
                $this->lkup[strtolower($interface->getName())] = $class;
            }
            do { // register under own and parent class names
                $this->lkup[strtolower($classname)] = $class;
            } while ($classname=get_parent_class($classname));
        }
        return $this; // fluent interface
    }

    /**
     * Applies DI container to return class instance.
     *
     * @param string $class classname
     * @param array $args  construct args not covered by DI
     * @return object  class instance
     */
    function like($class,array $args=array())
    {
        $key = strtolower($class); // case-insensitive key
        // translate class from config
        $class = isset($this->lkup[$key]) ? $this->lkup[$key] : $class;
        if (is_object($class)) {
            return $class; // singleton defined
        }
        // create reflection
        $c_key = strtolower($class); // case-insensitive again, might be
                                     // different from lkup key if mapped to
                                     // different class
        if (!isset($this->cache[$c_key])) {
            $reflect = $this->cache[$c_key] = new ReflectionClass($class);
        } else {
            $reflect = $this->cache[$c_key];
        }
        // check can be instantiated
        if (!$reflect->isInstantiable()) {
            throw new RuntimeException("$class is not instantiable (check DI config)");
        }
        // examine constructor params
        $construct = $reflect->getConstructor();
        if (!$construct || $construct->getNumberOfParameters()==0) {
            return new $class(); // shortcut when no params
        }
        $c_args = array();
        foreach ($construct->getParameters() as $p) {
            $name = $p->getName();
            if (array_key_exists($name,$args)) {
                $c_args[] = $args[$name];
            } elseif (!$p->isOptional() && $hint=$p->getClass()) {
                $this->cache[strtolower($hint->getName())] = $hint;
                $c_args[] = $this->like($hint->getName());
                  // ^ populate deps by using DI!
            } elseif (!$p->isOptional()) {
                // not an optional arg, with no type hint. problem.
                throw new RuntimeException("No value for arg $name in class $class");
            }
        }
        return $reflect->newInstanceArgs($c_args);
    }

}

// ------------------------- FIND RULES

/**
 * A rule to find an item.
 */
interface T_Find_Rule
{

    /**
     * Finds an item.
     *
     * @param string $query
     * @param string $type
     * @return mixed  either the found item or false if not found
     */
    function find($query,$type=null);

}

/**
 * Load files from a particular directory.
 */
class T_Find_FileInDir implements T_Find_Rule
{

    /**
     * Directory from which to load.
     *
     * @var string
     */
    protected $dir;

    /**
     * Type of file.
     *
     * @var string
     */
    protected $type;

    /**
     * Filter to apply to query.
     *
     * @var function
     */
    protected $filter;

    /**
     * Create directory finder.
     *
     * @param string $dir
     * @param string $type
     * @param function $filter  optional filter to apply to search string
     */
    function __construct($dir,$type,$filter=null)
    {
        $this->dir = rtrim(rtrim($dir,'/'),'\\').DIRECTORY_SEPARATOR;
        $this->type = $type;
        $this->filter = $filter;
    }

    /**
     * Find an item.
     *
     * @param string $query
     * @param string $type  e.g. 'class'
     * @return mixed  either the found item or false if not found
     */
    function find($query,$type=null)
    {
        // skip if types do not match
        if ($type && $this->type && strcmp($type,$this->type)!==0) return false;
        // try to find filepath
        if ($this->filter) $filter->transform($query);
        $path = $this->dir.$query.'.'.$type;
        if (is_file($path)) {
            return $path;
        }
        return false;
    }

}

/**
 * Load classes from a particular directory.
 */
class T_Find_ClassInDir extends T_Find_FileInDir
{

    /**
     * Prefix to strip.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Maps classnames to standard directory structure.
     *
     * e.g. Some_Class_Name => Some/Class/Name.php
     *
     * @param string $dir
     * @param string $prefix
     */
    function __construct($dir,$prefix=null)
    {
        parent::__construct($dir,substr(T_PHP_EXT,1));
        $this->prefix = $prefix;
    }

    /**
     * Find an item.
     *
     * @param string $query
     * @param string $type  e.g. 'class'
     * @return mixed  either the found item or false if not found
     */
    function find($query,$type=null)
    {
        // check prefix matches
        if ($this->prefix &&
            strncmp($query,$this->prefix,strlen($this->prefix))!==0) {
            return false;
        }
        $query = str_replace('_',DIRECTORY_SEPARATOR,substr($query,strlen($this->prefix)));
        return parent::find($query,$type);
    }

}

// ------------------------- ENVIRONMENT

/**
 * The environment code is operating in.
 */
interface T_Environment extends T_Factory,T_Find_Rule
{

    /**
     * Get an environment input.
     *
     * @param string $name
     * @return T_Cage_Array
     */
    function input($name);

    /**
     * Adds a rule to the environment.
     *
     * @param T_Find_Rule $rule
     * @return T_Environment  fluent
     */
    function addRule(T_Find_Rule $rule);

}

/**
 * Environment when operating within application.
 */
interface T_Environment_UrlContext extends T_Environment
{

    /**
     * Sets the server root URL.
     *
     * @param T_Url $root  web root
     */
    function setAppRoot($root);

    /**
     * Gets the server root URL.
     *
     * @return T_Url  web root
     */
    function getAppRoot();

    /**
     * Gets the URL of the request.
     *
     * @return T_Url  URL object
     */
    function getRequestUrl();

    /**
     * Gets the HTTP method.
     *
     * This returns the Http method (HEAD,GET,POST,PUT,DELETE) that the resource
     * has been requested with.
     *
     * @param function $filter  optional filter
     * @return string  HTTP request method
     */
    function getMethod($filter=null);

    /**
     * Whether the HTTPs method is a particular value.
     *
     * @return bool
     */
    function isMethod($method);

    /**
     * Is the request a XMLHttpRequest?
     *
     * Examines the headers to see if the use of XMLHttpRequest is flagged in
     * the X-Requested-With header. This is usually set by the standard JS
     * libraries like jQuery, Prototype, etc.
     *
     * @return bool
     */
    function isAjax();

}

/**
 * Environment for code execution.
 */
abstract class T_Environment_Autoload extends T_Factory_Di
                                      implements T_Environment
{

    /**
     * Stack of rules.
     *
     * @var T_Find_Rule
     */
    protected $stack = array();

    /**
     * Environment inputs
     *
     * @var array
     */
    protected $input = false;

    /**
     * Setup autoload and error handling.
     */
    function __construct()
    {
        parent::__construct();
        spl_autoload_register(array($this,'handleAutoload'));
        set_exception_handler(array($this,'handleException'));
        set_error_handler(array($this,'handleError'));
        date_default_timezone_set('UTC');
        if (version_compare(PHP_VERSION,'5.3.0','<')) set_magic_quotes_runtime(0);
        ini_set('auto_detect_line_endings',1);
        $this->willUse(new T_Exception_Handler()); // single exception handler
    }

    /**
     * Find an item.
     *
     * @param string $query
     * @param string $type  e.g. 'class'
     * @return mixed  either the found item or false if not found
     */
    function find($query,$type=null)
    {
        foreach ($this->stack as $rule) {
            if (($found=$rule->find($query,$type))!==false) return $found;
        }
        return false;
    }

    /**
     * Get an environment input.
     *
     * @param string $name
     * @return T_Cage_Array
     */
    function input($name)
    {
        if (false===$this->input) {
            $this->input = $this->parseInput();
        }
        if (!array_key_exists($name,$this->input)) {
            return null;
        }
        return $this->input[$name];
    }

    /**
     * Initialise input.
     *
     * @return array
     */
    abstract protected function parseInput();

    /**
     * Adds a rule to the stack.
     *
     * @param T_Find_Rule $rule
     * @return T_Find  fluent
     */
    function addRule(T_Find_Rule $rule)
    {
        array_unshift($this->stack,$rule);
        return $this;
    }

    /**
     * Autoload
     *
     * @param string $name  classname
     * @return bool whether the class could be loaded.
     */
    function handleAutoload($name)
    {
        $found = $this->find($name,'php');
        if ($found) {
            require_once($found);
            return true;
        }
        return false;
    }

    /**
     * Custom error handler.
     *
     * @param int $code  error code
     * @param string $string  error message
     * @param string $filename  filename where the error occurred
     * @param int $line  line number of error
     * @param array $scope  variable values in the scope of the error
     * @return bool confirms whether the function has handled the error or not
     */
    function handleError($code,$string,$filename,$line,$scope)
    {
        $level = ini_get('error_reporting');
        if (!($code & $level)) {
            return true;  // error_reporting is off for this code
        }
        // convert error to an exception
        $e = new ErrorException($string,0,$code,$filename,$line);
        // try to handle error using in-built exception handler
        if (class_exists('T_Exception_Handler') &&
            $this->like('T_Exception_Handler')->handle($e)) {
            return true; // error has been handled
        }
        // otherwise, throw and hope it gets caught.
        throw $e;
    }

    /**
     * Handle exception.
     *
     * @param Exception $exception
     * @return bool  whether the exception was handled.
     */
    function handleException($exception)
    {
        if ($exception instanceof ErrorException) {
            // we have already tried to handle this exception in the error
            // handler before it was thrown so we don't repeat ourselves.
            $handled = false;
        } else {
            $handled = $this->like('T_Exception_Handler')
                            ->handle($exception);
        }
        if (!$handled) {
            if (ini_get('display_errors')) echo $exception;
        }
        return $handled;
    }

}

/**
 * Environment when operating at the command line.
 */
class T_Environment_Terminal extends T_Environment_Autoload
{

    /**
     * Setup terminal environment.
     */
    function __construct()
    {
        parent::__construct();

        // Handle fact that we may not be using the php cli environment. If we
        // are using CGI, we need to make some corrections.
        // @see http://www.sitepoint.com/print/php-command-line-1/
        if (php_sapi_name() == 'cgi') {

            // output buffering
            @ob_end_flush();
            ob_implicit_flush(TRUE);

            // PHP ini settings
            set_time_limit(0);
            ini_set('track_errors',1);
            ini_set('html_errors',0);

            // define stream constants
            define('STDIN', fopen('php://stdin','r'));
            define('STDOUT', fopen('php://stdout','w'));
            define('STDERR', fopen('php://stderr','w'));

            // close the streams on script termination
            register_shutdown_function(
                create_function('',
                'fclose(STDIN); fclose(STDOUT); fclose(STDERR); return true;')
                );
        }
    }

    /**
     * Parse command line inputs.
     *
     * By default, the terminal environment parses all single letter flags as
     * a optional input.
     *
     * @return array
     */
    protected function parseInput()
    {
        /*
        Arguments are difficult to handle. The getopts function is the best way
        but is not available on windows until PHP 5.3.0. Until this, leave the
        child evenvironment to define its own arg handling.
        */
        return array();
    }

}

/**
 * Environment when operating under an HTTP request.
 */
class T_Environment_Http extends T_Environment_Autoload
                         implements T_Environment_UrlContext
{

    /**
     * App root URL.
     *
     * @var T_Url
     */
    protected $root = null;

    /**
     * URL of request.
     *
     * @var T_Url
     */
    protected $url = null;

    /**
     * Request method.
     *
     * @var string
     */
    protected $method = null;

    /**
     * Create HTTP environment.
     */
    function __construct()
    {
        parent::__construct();
        // add session handler to factory
        $this->willUse(new T_Session_Handler());
    }

    /**
     * Sets the server root URL.
     *
     * @param T_Url $root  web root
     */
    function setAppRoot($root)
    {
        $this->root = $root;
        if ($c=$this->input('COOKIE')) $c->setRootUrl($root);
        return $this;
    }

    /**
     * Gets the server root URL.
     *
     * @return T_Url  web root
     */
    function getAppRoot()
    {
        if ($this->root) {
            return clone($this->root);
        } else {
            return null;
        }
    }

    /**
     * Gets the URL of the request.
     *
     * @return T_Url  URL object
     */
    function getRequestUrl()
    {
        $this->parseUrlAndMethod(); // ini method and URL
        return ($this->url) ? clone($this->url) : false;
    }

    /**
     * Gets the HTTP method.
     *
     * This returns the Http method (HEAD,GET,POST,PUT,DELETE) that the resource
     * has been requested with.
     *
     * @param function $filter  optional filter
     * @return string  HTTP request method
     */
    function getMethod($filter=null)
    {
        $this->parseUrlAndMethod(); // ini method and URL
        return _transform($this->method,$filter);
    }

    /**
     * Whether the HTTPs method is a particular value.
     *
     * @return bool
     */
    function isMethod($method)
    {
        $this->parseUrlAndMethod(); // ini method and URL
        return strcasecmp($this->method,$method)===0;
    }

    /**
     * Is the request a XMLHttpRequest?
     *
     * Examines the headers to see if the use of XMLHttpRequest is flagged in
     * the X-Requested-With header. This is usually set by the standard JS
     * libraries like jQuery, Prototype, etc.
     *
     * @return bool
     */
    function isAjax()
    {
        $key = 'HTTP_X_REQUESTED_WITH';
        if ($server=$this->input('SERVER')) {
            if ($server->exists($key)) {
                $val = $server->asScalar($key)->uncage();
                return strcasecmp($val,'XMLHttpRequest')===0;
            }
        }
        return false;
    }

    /**
     * Parse GET, POST, FILES and COOKIE inputs.
     *
     * @return array
     */
    protected function parseInput()
    {
        $get = $_GET;
        $post = $_POST;
        $cookie = $_COOKIE;
        if (get_magic_quotes_gpc()) {
            $f = new T_Filter_NoMagicQuotes();
            $get = $f->transform($get);
            $post = $f->transform($post);
            $cookie = $f->transform($cookie);
        }

        $data = array();
        $data['GET'] = new T_Cage_Array($get);
        $files = isset($_FILES) ? $_FILES : array();
        $data['POST'] = new T_Cage_Post($post,$files);
        $data['SERVER'] = new T_Cage_Array($_SERVER);
        $data['COOKIE'] = new T_Cage_Cookie($cookie);

        return $data;
    }

    /**
     * Initialise the request.
     *
     * @return void
     */
    protected function parseUrlAndMethod()
    {
        if (!is_null($this->method)) return; // already initialised
        $server = $this->input('SERVER');

        try {
            $scheme = $this->isSsl($server) ? 'https' : 'http';

            /**
             * Get hostname.
             *
             * A possible alternative to SERVER_NAME is HTTP_HOST but this
             * value is set from the user via a 'Host:' header and cannot be
             * trusted. If it was used it might be possible to create login forms
             * that get submitted to another server for example. SERVER_NAME is
             * taken from the Virtual Host settings and cannot be influenced by
             * the user.
             */
            $host = $server->asScalar('SERVER_NAME')->uncage();

            /**
             * Get the request URI.
             *
             * We use the global REQUEST_URI with the query path and fragment
             * stripped off to use as the controller mapping path. Again, XSS
             * attacks might be included in this path, but they are useless as they
             * won't match a controller mapping and a 404 error will result.
             */
            $path = $server->asScalar('REQUEST_URI')
                           ->filter(new T_Filter_UrlPath())
                           ->uncage();

            /**
             * Get request method.
             */
            $method = $server->asScalar('REQUEST_METHOD')
                             ->filter('mb_strtoupper')
                             ->uncage();

            /**
             * Set URL, subspace and method
             */
            $this->url = new T_Url($scheme,$host,$path);
            $this->method = $method;

        } catch (T_Exception_Cage $e) {
            // unable to parse out the server URL.
            $this->url = false;
            $this->method = false;
        }
    }

    /**
     * Detects whether the request is HTTPS
     *
     * @param T_Cage_Array $server  server superglobal data
     * @return bool  whether the request has been made over a secure connection
     */
    protected function isSsl(T_Cage_Array $server)
    {
        // extract HTTPS status and port number from $_SERVER
        $https = null;
        if ($server->exists('HTTPS')) {
            $https = $server->asScalar('HTTPS')->uncage();
        }
        $port = null;
        if ($server->exists('SERVER_PORT')) {
            $port = $server->asScalar('SERVER_PORT')
                           ->filter(new T_Validate_Int())
                           ->uncage();
        }
        // test data
        return (strcasecmp($https,'on')===0 || $https==1 || $port==443);
    }

}

// -------- DEFAULT HANDLERS (session, exception)

/**
 * Encapsulates session data.
 *
 * @package core
 */
class T_Session_Handler
{

    /**
     * Namespace delimiter.
     */
    const NS_DELIMIT = '|';

    /**
     * Session drivers.
     *
     * @var T_Session_Driver[]
     */
    protected $drivers = array();

    /**
     * Default driver key.
     *
     * @var int
     */
    protected $default_driver = false;

    /**
     * The driver keys that are responsible for each namespace.
     *
     * @var array
     */
    protected $ns_driver = array();

    /**
     * Data without a namespace.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Stores the text namespaces for each index.
     *
     * @var array
     */
    protected $ns = array();

    /**
     * Whether SESSION has already been started.
     *
     * @var bool
     */
    protected $ini = false;

    /**
     * Initialise session.
     *
     * @return void
     */
    protected function ini()
    {
        if ($this->ini) {
            return $this;
        }
        // if no drivers are present, add a default normal session driver.
        if ($this->default_driver===false) {
            if (count($this->drivers)>0) {
                $this->default_driver = key($this->drivers); // last driver added as default
            } else {
                $this->addDriver(new T_Session_NativeDriver());
            }
        }
        $this->ini = true; // this must be after the possible addDriver() call above,
                           // but *before* any 'set' calls below.

        // import data from all drivers into session
        foreach ($this->drivers as $d) {
            foreach ($d->get() as $name => $val) {
                $this->set($name,$val);
            }
        }

        return $this;
    }

    /**
     * Close and end session.
     */
    function __destruct()
    {
        if (!$this->ini) return;
        // distribute data
        $data = array();
        foreach (array_keys($this->drivers) as $key) {
            $data[$key] = array();
        }
        $data[$this->default_driver] = $this->data;
        foreach ($this->ns as $name => $values) {
            if (isset($this->ns_driver[$name])) {
                $data[$this->ns_driver[$name]] += $values;
            } else {
                $data[$this->default_driver] += $values;
            }
        }
        // save data
        foreach ($this->drivers as $key => $d) {
            $d->save($data[$key]);
        }
    }

    /**
     * Splits offset into namespace and key.
     *
     * @return array
     */
    protected function splitKey($key)
    {
        if (strpos($key,self::NS_DELIMIT)===false) {
            return array(null,$key);
        } else {
            $bits = explode(self::NS_DELIMIT,$key);
            return array(array_shift($bits),$key);
        }
    }

    /**
     * Whether a key exists.
     *
     * @param mixed $key  string or integer array key
     * @return bool  whether or not the key exists in the array
     */
    function exists($key)
    {
        list($ns,$key) = $this->ini()->splitKey($key);
        if (is_null($ns)) {
            return array_key_exists($key,$this->data);
        } else {
            return isset($this->ns[$ns]) && array_key_exists($key,$this->ns[$ns]);
        }
    }

    /**
     * Set definition.
     *
     * @param mixed $key  string or integer array key
     * @param mixed $value  the value to insert at this key position
     */
    function set($key,$value)
    {
        list($ns,$key) = $this->ini()->splitKey($key);
        if (is_null($ns)) {
            $this->data[$key] = $value;
        } else {
            $this->ns[$ns][$key] = $value;
        }
        return $this;
    }


    /**
     * Gets a value.
     *
     * @param mixed $key  string or integer array key
     * @param function $filter  optional filter
     * @return mixed
     */
    function get($key,$filter=null)
    {
        list($ns,$key) = $this->ini()->splitKey($key);
        if (is_null($ns)) {
            return _transform($this->data[$key],$filter);
        } else {
            return _transform($this->ns[$ns][$key],$filter);
        }
    }

    /**
     * delete definition.
     *
     * @param mixed $key  string or integer array key
     */
    function delete($key)
    {
        list($ns,$key) = $this->ini()->splitKey($key);
        if (is_null($ns)) {
            unset($this->data[$key]);
        } else {
            if (isset($this->ns[$ns])) unset($this->ns[$ns][$key]);
        }
        return $this;
    }

    /**
     * Regenerate session.
     *
     * @return T_Session_Handler  fluent interface
     */
    function regenerate()
    {
        $this->ini();
        foreach ($this->drivers as $d) {
        	$d->regenerate();
        }
        return $this;
    }

    /**
     * Destroy session.
     *
     * @return T_Session_Handler  fluent interface
     */
    function destroy()
    {
        $this->ini();
        foreach ($this->drivers as $d) {
        	$d->destroy();
        }
        return $this;
    }

    /**
     * Sets a session driver.
     *
     * This can be for the default data (with second argument null), a single
     * or multiple namespaces. This method must be called to configure the
     * session mechanism *before* it has been started (which is done
     * automatically when creating or settings variables).
     *
     * @param T_Session_Driver $driver
     * @param mixed $ns  single namespace, or array of namespaces
     */
    function addDriver(T_Session_Driver $driver,$ns=null)
    {
        if ($this->ini) {
            throw new BadFunctionCallException('Session has already started');
        }
        // add driver to existing array
        $key = false;
        foreach ($this->drivers as $k => $d) {
            if ($d===$driver) $key = $k;
        }
        if ($key===false) {
            $key = count($this->drivers);
            $this->drivers[$key] = $driver;
        }
        // match up associations
        if (is_null($ns)) {
            $this->default_driver = $key;
        } else {
            if (!is_array($ns)) $ns = array($ns);
            foreach ($ns as $name) {
                $this->ns_driver[$name] = $key;
            }
        }
        return $this;
    }

}

/**
 * Exception Handler.
 *
 * @package core
 */
class T_Exception_Handler
{

    /**
     * Chain of exception handlers.
     *
     * @var T_Exception_Handler_Action[]
     */
    protected $chain = array();

    /**
     * Append an exception action to the chain.
     *
     * @param T_Exception_Handler_Action $action
     * @return T_Exception_Handler  fluent interface
     */
    function append(T_Exception_Handler_Action $action)
    {
        $this->chain[] = $action;
        return $this;
    }

    /**
     * Prepend an exception action to the chain.
     *
     * @param T_Exception_Handler_Action $action
     * @return T_Exception_Handler  fluent interface
     */
    function prepend(T_Exception_Handler_Action $action)
    {
        array_unshift($this->chain,$action);
        return $this;
    }

    /**
     * Attempt to handle the exception.
     *
     * @param Exception $e
     * @return bool  whether exception was handled or not
     */
    function handle(Exception $e)
    {
        foreach ($this->chain as $action) {
            if ($action->handle($e)) return true;
        }
        return false;
    }

}
