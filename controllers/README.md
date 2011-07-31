Controllers
===========

The controllers package provides a way to route HTTP requests via a front controller. URLs are mapped to controllers using a nested chain based on the requested path, enforcing best practice RESTful URLs. It provides a way to encapsulate the response, and add response filters to handle compression, buffering, client caching, etc.

Front Controller Setup
----------------------

The controllers package requires that HTTP requests are routed through a "front controller" (normally an index.php file in web root). The most common way to do that is using Apache mod_rewrite rules, for example the following .htaccess file will route all requests that don't match an existing file or directory to index.php:

    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME}  -d [OR]
    RewriteCond %{REQUEST_FILENAME}  -f
    RewriteRule  ^.*$ - [L]
    RewriteRule ^.*$ index.php [L]

Once the request has routed to the front controller, the HTTP request is encapsulated by creating a T_Controller_Request object with reference to the [/how-to/environments HTTP environment]. This object parses and provides access to the request & root URL, HTTP method, and acts as the **context** for the root controller. A root controller is created and then "dispatched".

    <?php
    // bootstrap app then..
    $env = new T_Environment_Http;
    // .. [snip] ..
    $app = new Root_Controller(new T_Controller_Request($env));
    $app->dispatch();
    ?>

The Roles of a Controller
-------------------------

A "controller" is simply a class that handles a request, but it acts in a number of different roles:

* Each controller is an [/how-to/environments HTTP environment], and as such acts as factory, asset locator, etc for a request that is routed to it.
* Each controller acts as the context of an HTTP request. Controllers are routed as a chain, and the **context** is passed to a controller when it is created to let it know standard parameters about the request.
* Each controller finally also acts to actually handle a request, and every controller is dispatchable (i.e. can be the start of a controller chain).

While the primary function of a controller is to handle a request, each controller provides an HTTP environment within which your code can run, and acts as the context for the next controller in the routing chain is to continue.

A Simple Controller
-------------------

The default controller is the class T_Controller, which maps a URL method to named methods in the class itself. For example, a 'Hello World' controller would look something like:

    <?php
    class HelloWorld extends T_Controller
    {
        function GET($response)
        {
            $response->setContent('Hello World');
        }
        function HEAD($response)
        {
            $response->setContent(null);
        }
    }
    ?>

Note how different HTTP methods are handled explictally: a GET request results in the content being provided, a HEAD request results in the normal headers being returned but no content, and other methods such as POST, DELETE and PUT will result in the default action which is a 501 response.

The Controller Chain
--------------------

Dispatching  a controller initiates a controller "chain", where each directory in the URL path is mapped to a child controller. If any part of the URL that is already mapped, a controller will use it's mapToClassname method to find the classname from the URL string. If this method return false, a 404 response is issued.

For example, we'll create a site with a home page and an articles section:

    <?php
    class My_Controller extends T_Controller
    {
        // default HEAD method provides no content
        function HEAD($response)
        {
            $response->setContent(null);
        }
    }
    class Root extends My_Controller
    {
        function GET($response)
        {
            $response->setContent('home');
        }
        function mapToClassname($name)
        {
            return $name==='article' ? 'ArticleList' : false;
        }
    }
    class ArticleList extends My_Controller
    {
        function GET($response)
        {
            $response->setContent('some articles');
        }
    }
    /*
     GET /           --> Root::GET() executed
     GET /article    --> ArticleList::GET() executed
     GET /wrong      --> 404 (Root child mapping failure)
     GET /article/no --> 404 (ArticleList child mapping failure)
    */
    ?>

The real value of handling controllers via a chain is when handling dynamic URLs, and two common examples are described below.

### Mapping Through a Parameter

Continuing our article site started above, lets say we want to provide a URL structure like:

    /article                     List of articles
    /article/some-name           Single article
    /article/some-name/comments  Single article comments

The article is identified by a URL string, and this portion of the URL will be dynamic. However, we still need to be able to map **through** this dynamic parameter to the comments page for a particular article.

    <?php
    class ArticleList extends My_Controller
    {
        function GET($response)
        {
            $response->setContent('list of articles');
        }
        function mapToClassname($name)
        {
            return 'Article';
             // always map to article controller
        }
    }
    class Article extends My_Controller
    {
        public $article;
        function handleRequest($response)
        {
            $name = _end($this->getUrl()->getPath());
            try {
                $this->article = $this->like('ArticleGateway')
                                        ->getByUrl($name);
            } catch (Exception $e) {
                $this->respondWithStatus(404,$response);
            }
            parent::handleRequest($response);
        }
        function GET($response)
        {
            $response->setContent($this->article->getTitle());
        }
        function mapToClassname($name)
        {
            return $name==='comments' ? 'ArticleComments' : false;
        }
    }
    class ArticleComments extends My_Controller
    {
        function GET($response)
        {
            $article = $this->context->article;
              // article accessible via parent context
            $response->setContent($article->getComments());
        }
    }
    ?>

Note how...

* We map a dynamic URL string (article name) to a controller by always returning that controller name whatever the input to ArticleList::mapToClassname().
* The Article controller loads the article in it's handleRequest() method. This method is always executed whether the controller is executed or passes control to a child in the chain.
* The article name is found in Article:handleRequest() by examining the last element in that controller's URL. An article gateway is created (using the controller as an environment factory) and if an article cannot be retrieved a 404 is returned.
* Because we set the Article::article property as public, it is accessible via the context attribute of it's children. So the ArticleComments controller already has access to the article we want the comments from.

### The Controller "Intercept"

Each controller has a handleRequest() method that is executed whenever that controller is invoked during a request: this is either when the controller is part of the chain, or when the controller is executed. By adding to this method in a particular controller it is possible to specify code to run at any point in a URL structure.

For example, say we want to password protect the articles area. We can add a login check to the ArticleList::handleRequest() method, and if the user is not logged in we can re-route the request to a login controller. We can make this login controller recursively map to itself so whatever the URL it always gets executed.

    <?php
    class ArticleList extends My_Controller
    {
        function handleRequest($response)
        {
            if (/* not logged in */) {
                $this->delegateTo('Login');
            }
            parent::handleRequest($response);
        }
        /* ... [snip] ... */
    }
    class Login extends My_Controller
    {
        function GET($response)
        {
            $response->setContent('login form');
        }
        function POST($response)
        {
            /* ... process login form ... */
        }
        function mapToClassname($name)
        {
            return get_class($this); // map to self
        }
    }
    ?>

By doing this we have intercepted the request as it passes through a particular controller, which means any request that passes through the ArticleList controller (i.e. /article/*) will be rerouted if the user is not authenticated.

*IMPORTANT* Note that the native T_Controller::handleRequest() method is responsible for continuing the controller chain if necessary. If you do add extra code to this method in one of your controllers make sure to make a call at the end of parent::handleRequest($response) otherwise the chain will stop at that point.

Working with the HTTP Response
------------------------------

The T_Response class encapsulates the HTTP response, and we have already seen it has a setContent() method to set the body content. You can set headers by a similar setHeader() method, and the HTTP status (e.g. 200 OK, or 404 No Found) is set in the constructor or changed using setStatus().

    <?php
    // pdf file response
    $response = new T_Response(200);
    $response->setHeader('Content-Type','application/pdf')
             ->setContent(file_get_contents('my.pdf'));
    ?>

### Response Filters

The response supports the concept of filters which act on the response when it is sent. There are inbuilt filters to support client caching (T_Response_ClientCache), response compression (T_Response_Compression) and conditional GET negotiation (T_Response_ConditionalGet). For example, to add gzip compression when supported by the client:

    <?php
    $response = new T_Response(200);
    $response->appendFilter(new T_Response_Compression($env));
    ?>

Handling Request Failure
------------------------

Some requests fail, either by an incorrect URL or a problem in your application. In these cases the controller hierachy allows you to react to the problem using HTTP with the PHP exception model. The response object is actually an extended exception which can be thrown. If an error is encountered within a controller, you can abort the current response, and create a throw an alternative. For example:

    <?php
    class ArticleList extends My_Controller
    {
        function GET($response)
        {
            if (/* is an error */) {
                $response->abort();
                throw new T_Response(503); // 503 Internal Server Error
            }
            $response->setContent('some articles');
        }
    }
    ?>

Once a alternative response is thrown, the controller chain is broken out from and that response is immediately sent to the user. As an alternative, the inbuilt controller provides a method respondWithStatus() that does this for you. The advantage to using this method is since you are piping all the error conditions through a method is it easy later to add custom 404 or other error content.

    <?php
    class ArticleList extends My_Controller
    {
        function GET($response)
        {
            if (/* is an error */) {
                $this->respondWithStatus(503,$response);
                  // ^ throws new response
            }
            $response->setContent('some articles');
        }
    }
    ?>

Building Your Own Controller Behaviour
--------------------------------------

Controllers are the core wiring behind your application, and for all but the simplest projects I would recommend you create your own base controller class. At a minimum I would usually define something like:

    <?php
    class My_Controller extends T_Controller
    {
        /**
         * Default HEAD method provides headers, but no content.
         */
        protected function HEAD($response)
        {
            $response->setContent(null);
        }
        /**
         * Provide custom 404 response from template.
         */
        protected function respondWithStatus($status,T_Response $response)
        {
            if ($status==404) {
                $response->abort();
                $new = new T_Response(404);
                $new->setContent(new T_Template_File($this->find('404','tpl')));
                throw $new;
            }
            parent::respondWithStatus($status,$response);
        }
    }
    ?>

The above class extends the built-in T_Controller class, but if you wish you can create your own controllers which will have their own behaviour (for example, they do not need to chain).

### Anatomy of a Controller

A controller class built from scratch must meet the interface contracts of each of it's three roles:

* T_Environment_UrlContext to provide an environment
* T_Controller_Context to act as a context
* T_Controller_Action to handle a request

Environments are already discussed elsewhere and a controller will usually simply proxy these methods to it's context (i.e. pass the call back up the controller chain to a single application environment).

To act as a context for other controllers the class must expose it's own URL (via the getUrl() method), and also provide information about the URL space that it has not yet mapped: the getSubspace() method returns an array of the portions of the URL that have not already been handled by the current context.

To execute a request, the controller must provide a handleRequest() method which is passed the current response object, and a dispatch() method to start a controller chain.
