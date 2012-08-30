<?php
/**
 * Contains the T_Controller_Request class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * HTTP Request Context.
 *
 * This object is created in the dispatcher to encapsulate a HTTP request in a
 * controller context wrapper.
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
class T_Controller_Request implements T_Controller_Context
{

    /**
     * Environment.
     *
     * @var T_Environment
     */
    protected $env;

    /**
     * URL of current context.
     *
     * @var T_Url
     */
    protected $url;

    /**
     * Subspace still to be mapped.
     *
     * @var array
     */
    protected $subspace;

    /**
     * Encapsulate request.
     *
     * The constructor encapsulates the request into a root URL and still-to-map
     * subpace of URL segments by parsing the $_SERVER superglobal. This is
     * undertaken with some care as the user can spoof most bits of the
     * superglobal by munging the headers sent.
     *
     * @throws T_Response  response to send if request disallowed
     * @throws T_Exception_Controller  internal parse error occurred
     */
    function __construct(T_Environment_UrlContext $env)
    {
        $this->env = $env;

        $url = $this->getRequestUrl();
        if (!$url) throw new T_Exception_Controller('Could not parse request URL');

        /**
         * Get document root of this application.
         *
         * The document root of the application is the directory name of the
         * front controller. We can get the actual document root of the front
         * controller using $_SERVER['PHP_SELF'] directory. This can be
         * influenced by the user when requesting the front controller directly.
         */
        $root = $this->input('SERVER')->asScalar('PHP_SELF')
                                      ->filter(new T_Filter_UrlPath())
                                      ->uncage();
        $key = array_search('index'.T_PHP_EXT,$root);
        if ($key===false) {
            $root = array();
        } else {
            $root = array_slice($root,0,$key);
        }
        
        /**
         * Get the request URI bits.
         */
        $scheme = $url->getScheme();
        $host = $url->getHost();
        $path = $url->getPath();
        $path = array_values(array_diff_assoc($path,$root));
          // strip root path by comparing values and keys

        /**
         * Check request method.
         */
        $method = $this->getMethod();
        if (!in_array($method,array('HEAD','GET','POST','PUT','DELETE'))) {
            throw $this->like('T_Response',array('status'=>405));  // method not allowed
        }

        /**
         * Set URL, subspace and method
         */
        $this->url = new T_Url($scheme,$host,$root);
        $this->subspace = $path;
    }

    /**
     * Gets the URL of the current context.
     *
     * @return T_Url  URL object
     */
    function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets the sub space of the desired URL still to be mapped.
     *
     * @return array  path segments still to be mapped.
     */
    function getSubspace()
    {
        return $this->subspace;
    }

    /**
     * Gets the HTTP scheme this controller is coerced to (if any).
     *
     * @return string
     */
    function getCoerceScheme()
    {
        return null;
    }

    /**
     * Request context is 'delegated' in that the first controller should not consume url.
     *
     * @return bool
     */
    function isDelegated()
    {
        return true;
    }

    /**
     * Creates a class instance.
     *
     * @param string $class classname
     * @param array $args  construct args
     * @return object  class instance
     */
    function like($class,array $args=array())
    {
        return $this->env->like($class,$args);
    }

    /**
     * Configure DI container.
     *
     * This method can only be safely used when the factory is a DI container
     * (as the willUse method is not part of the T_Factory interface).
     *
     * @param string $class  classname
     * @param string $alias  additional alias
     */
    function willUse($class,$alias=null)
    {
        $this->env->willUse($class,$alias);
        return $this;
    }

    /**
     * Finds an item.
     *
     * @param string $query
     * @param string $type
     * @return mixed  either the found item or false if not found
     */
    function find($query,$type=null)
    {
        return $this->env->find($query,$type);
    }

    /**
     * Adds a rule to the environment.
     *
     * @param T_Find_Rule $rule
     * @return T_Environment  fluent
     */
    function addRule(T_Find_Rule $rule)
    {
        $this->env->addRule($rule);
        return $this;
    }

    /**
     * Get an environment input.
     *
     * @param string $name
     * @return T_Cage_Array
     */
    function input($name)
    {
        return $this->env->input($name);
    }

    /**
     * Sets the server root URL.
     *
     * @param T_Url $root  web root
     */
    function setAppRoot($root)
    {
        $this->env->setAppRoot($root);
    }

    /**
     * Gets the server root URL.
     *
     * @return T_Url  web root
     */
    function getAppRoot()
    {
        return $this->env->getAppRoot();
    }

    /**
     * Gets the URL of the request.
     *
     * @return T_Url  URL object
     */
    function getRequestUrl()
    {
        return $this->env->getRequestUrl();
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
        return $this->env->getMethod($filter);
    }

    /**
     * Whether the HTTPs method is a particular value.
     *
     * @return bool
     */
    function isMethod($method)
    {
        return $this->env->isMethod($method);
    }

    /**
     * Is the request an Ajax request?
     *
     * @return bool
     */
    function isAjax()
    {
        return $this->env->isAjax();
    }

}
