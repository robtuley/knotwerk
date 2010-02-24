<?php
/**
 * Contains the T_Controller class.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Request Controller.
 *
 * This is the base controller class. The controller pass responsibility for a
 * request down a chain of command until the correct controller is reached. They
 * maintain a context and regsiter as the responsibility is passed forward that
 * is available in each controller object.
 *
 * @package controllers
 */
class T_Controller implements T_Controller_Action
{

    /**
     * Controller Context
     *
     * The controller context provides access to details of the request, and
     * essentially summaries the mapping that has occurred so far. Use it to
     * extract the URL and subspace of the previous controller in the chain.
     *
     * @var T_Controller_Context
     */
    protected $context;

    /**
     * The URL of this controller.
     *
     * @var T_Url
     */
    protected $url;

    /**
     * The subspace still to be mapped after this controller.
     *
     * @var array
     */
    protected $subspace;

    /**
     * Delegate to this controller.
     *
     * @var false|string
     */
    protected $delegate_to = false;

    /**
     * Coerces a particular scheme.
     *
     * @var string
     */
    protected $coerce_scheme = null;

    /**
     * Create controller from previous context.
     *
     * This default controller action clones URL and subspace from the previous
     * controller context. If it is consuming the URL path (as is normal), it
     * shifts a single value out from the subspace and moves it onto its own URL.
     * Sometimes, the request is "delegated" sideways in the controller stack, and
     * in this case the controller simply takes over from the parent and does not
     * pop any bits off the pathname.
     *
     * @param T_Controller_Context $context  context
     */
    function __construct(T_Controller_Context $context)
    {
        $this->context  = $context;
        $this->url = clone($context->getUrl());
        $this->subspace = $context->getSubspace();
        if (!$context->isDelegated()) {
            $name = array_shift($this->subspace);
            if (strlen($name)==0) {
                throw new T_Exception_Controller('no subspace stack');
            }
            $this->url->appendPath($name);
        }
        $this->coerceScheme($context->getCoerceScheme()); // inherit any scheme coerce
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
     * Delegate control to another controller, without consuming the URL.
     *
     * This gives the user the possibility of routing to resquest to another
     * controller without it being mapped directly from the URL. The method
     * should be used sparingly, but is useful when sometimes it is necessary
     * to route the request to a secondary controller based on state rather
     * than URL (e.g. logins).
     *
     * To have any effect, the delegation must occur before the controller
     * handles the request..!
     *
     * @param string $name  controller name
     */
    function delegate($name)
    {
        $this->delegate_to = $name;
        return $this;
    }

    /**
     * Whether this controller is delegated.
     *
     * @return bool
     */
    function isDelegated()
    {
        return $this->delegate_to!==false;
    }

    /**
     * Dispatch the request to controller chain.
     *
     * This method parses the request, dispatches it down the chain of
     * controllers and then sends the response. It is called explicitally from
     * the front controller:
     *
     * <code>
     * $app = new Root();
     * $app->dispatch();
     * </code>
     */
    function dispatch()
    {
        try {
            $this->setAppRoot($this->getUrl());
            $response = $this->like('T_Response');
            $this->handleRequest($response);
            $response->send();
        } catch (T_Response $alt) {
            // check that previous response has been aborted.
            if (isset($response) && !$response->isAborted()) {
                $msg = 'Original response was never aborted.';
                throw new RuntimeException($msg);
            }
            // send alternative response
            $alt->send();
        }
    }

    /**
     * Handles the request.
     *
     * Method to handle the request. The function may delegate to a
     * sub-controller, or execute the request itself.
     *
     * @param T_Response $response  response to send to current request
     * @throws T_Response  alternative response in exceptional circumstances
     */
    function handleRequest($response)
    {
        $next = $this->findNext($response);
        if ($next===false) {
            $this->execute($response);
        } else {
            $next->handleRequest($response);
        }
    }

    /**
     * Coerces the request into a particular scheme on controller execution.
     *
     * @param string $scheme
     * @return T_Controller  fluent interface
     */
    function coerceScheme($scheme)
    {
        $this->coerce_scheme = $scheme;
        return $this;
    }

    /**
     * Gets the HTTP scheme this controller is coerced to.
     *
     * @return string
     */
    function getCoerceScheme()
    {
        return $this->coerce_scheme;
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
        return $this->context->like($class,$args);
    }

    /**
     * Configure DI container.
     *
     * This method can only be safely used when the base factory is a
     * DI container (as the willUse method is not part of the T_Factory
     * interface).
     *
     * @param string $class  classname
     * @param string $alias  additional alias
     */
    function willUse($class,$alias=null)
    {
        $this->context->willUse($class,$alias);
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
        return $this->context->find($query,$type);
    }

    /**
     * Adds a rule to the environment.
     *
     * @param T_Find_Rule $rule
     * @return T_Environment  fluent
     */
    function addRule(T_Find_Rule $rule)
    {
        $this->context->addRule($rule);
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
        return $this->context->input($name);
    }

    /**
     * Sets the server root URL.
     *
     * @param T_Url $root  web root
     */
    function setAppRoot($root)
    {
        $this->context->setAppRoot($root);
    }

    /**
     * Gets the server root URL.
     *
     * @return T_Url  web root
     */
    function getAppRoot()
    {
        return $this->context->getAppRoot();
    }

    /**
     * Gets the URL of the request.
     *
     * @return T_Url  URL object
     */
    function getRequestUrl()
    {
        return $this->context->getRequestUrl();
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
        return $this->context->getMethod($filter);
    }

    /**
     * Whether the HTTPs method is a particular value.
     *
     * @return bool
     */
    function isMethod($method)
    {
        return $this->context->isMethod($method);
    }

    /**
     * Is the request a XMLHttpRequest?
     *
     * @return bool
     */
    function isAjax()
    {
        return $this->context->isAjax();
    }

    /**
     * Executes the request.
     *
     * The execution of the request is delegated to child routines based on the
     * request method ('HEAD','GET','POST','PUT','DELETE').
     *
     * @param T_Response $response  response to build
     * @throws T_Response  alternative response in exceptional circumstances
     */
    protected function execute($response)
    {
        // redirect if not correct scheme
        if (!is_null($this->coerce_scheme) &&
            strcasecmp($this->getUrl()->getScheme(),$this->coerce_scheme)!==0) {
            $url = clone $this->getUrl();
            $url->setScheme($this->coerce_scheme);
            if ($get=$this->input('GET')) $url->setParameters($get->uncage());
               // maintain any GET parameters. Unfortunately, the URL fragment never
               // gets sent to the server from the browser so this information is
               // lost on re-direction.
            $response->abort();
            throw new T_Response_Redirect($url->getUrl());
        }
        // delegate to internal method
        $method = $this->getMethod();
        return $this->{$method}($response);
    }

    /**
     * Executes a GET request.
     *
     * @param T_Response $response  response to build.
     */
    protected function GET($response)
    {
        $this->respondWithStatus(501,$response);
    }

    /**
     * Executes a POST request.
     *
     * @param T_Response $response  response to build.
     */
    protected function POST($response)
    {
        $this->respondWithStatus(501,$response);
    }

    /**
     * Executes a HEAD request.
     *
     * @param T_Response $response  response to build.
     */
    protected function HEAD($response)
    {
        $this->respondWithStatus(501,$response);
    }

    /**
     * Executes a PUT request.
     *
     * @param T_Response $response  response to build.
     */
    protected function PUT($response)
    {
        $this->respondWithStatus(501,$response);
    }

    /**
     * Executes a DELETE request.
     *
     * @param T_Response $response  response to build.
     */
    protected function DELETE($response)
    {
        $this->respondWithStatus(501,$response);
    }

    /**
     * Finds the next controller.
     *
     * The default implementation to find the next controller is
     * is to look in the subspace: if this is empty, there are no more
     * controllers to be loaded. If there is some subspace, the method takes
     * the next subspace entry, maps it to a controller and loads the controller.
     *
     * @param T_Response $response  response to build
     * @return T_Controller  next controller or boolean false if none
     * @throws T_Response  alternative response in exceptional circumstances
     */
    protected function findNext($response)
    {
        if ($this->isDelegated()) {
            $classname = $this->delegate_to;
            return $this->like($classname,array('context'=>$this));
        } elseif (count($ss = $this->getSubspace())>0) {
            $classname = $this->mapToClassname($ss[0]);
            if ($classname===false) {
                $this->respondWithStatus(404,$response);
            } else {
                return $this->like($classname,array('context'=>$this));
            }
        }
        return false;
    }

    /**
     * Error occurred and alternative response required.
     *
     * If a custom 404 response, etc. is required, this controller method
     * can be overridden to build the custom response.
     *
     * @param int $status  Status code (e.g. 404,etc)
     * @param T_Response $response  old response (to abort)
     */
    protected function respondWithStatus($status,T_Response $response)
    {
        $response->abort();
        throw $this->like('T_Response',array('status'=>$status));
    }

    /**
     * This function maps a URL segment to a classname.
     *
     * This function maps a URL segment to an actual classname. It is where any
     * validation of what children are actually allowed. If a mapping can't be
     * found, the function should return false and that is the default function
     * defined here.
     *
     * @param string $name  URL segment to map to a classname
     * @return string   controller classname
     */
    protected function mapToClassname($name)
    {
        return false; // no sub-nav permitted: when something is permitted, this
                      // function should return the controller classname
    }

}
