Error Handling
==============

By bootstrapping the library no custom error handling is performed, and this is entirely left at your discretion. If you choose to use one of the in-built [/how-to/environments Environments] rather than define your own, then a customised error handling system is setup on creation.

    <?php
    require 'path/to/lib/bootstrap.php';
    // default error handling
    $env = new T_Environment_Http;
    // error handling environment has been customised
    ?>

Handling Exceptions
-------------------

Uncaught exceptions normally reach a default global exception handler which treats them as fatal errors. In the customised error handling environment, uncaught exceptions are handled instead by an instance of T_Exception_Handler. This class acts as simple proxy to a chain of exception handlers: any exception that bubbles up to the global scope is passed down the chain by T_Exception_Handler until it finds a sub handler that recognises and can handle it. If it is not handled by any helpers in the chain the system acts like the default PHP exception handler and displays the exception if php.ini display_errors is on, and then quits with a fatal error.

The value of this system is that you can define your own global exception handlers, and have a chance to intercept and react to the global exception before the script quits with a fatal error. It is important to note that you cannot prevent script termination after the handler has completed (it is not possible to recover normal code exception since the exception is uncaught), but you do have a chance to for example log the error, present a 'user friendly' error output page, etc.

For example, if we wanted to setup our environment to log all global exceptions to the system error log and then present an error page to the user:

    <?php
    class Error_Log implements T_Exception_Handler_Action {
      function handle(Exception $e) {
        syslog(LOG_ERR,$e->getMessage());
        return false; // continue down handler chain
      }
    }
    class Error_Display implements T_Exception_Handler_Action {
      function handle(Exception $e) {
        while (ob_get_level()) ob_end_clean(); // delete buffers
        echo 'User friendly error message (probably HTML)';
        return true; // make this the final handler
      }
    }

    $env = new T_Environment_Http;
    $env->like('T_Exception_Handler')
            ->append(new Error_Display) // can either append
            ->prepend(new Error_Log);   // or prepend to chain
    ?>

Handling Errors
---------------

The customised error handler will handle only those errors that are equal to or above the current php.ini (or runtime set) error_reporting level, so the @ operator and error_reporting function will work as expected. The handler will convert such errors into an [http://php.net/manual/class.errorexception.php ErrorException] and then pass them into the T_Exception_Handler chain. If they are not handled by the chain (i.e. by a sub-handler returning true), the handler will throw the error in the hope that it will be caught by the client code.

    <?php
    $env = new T_Environment_Http;
    try {
      trigger_error('msg',E_USER_WARNING);
      // never gets here
    } catch (Exception $e) {
      // error -> ErrorException and is caught here
    }
    ?>

It is important to note that although this gives you more powerful in-code error handling (as try-catch blocks can be used for both exceptions and standard PHP errors), it does mean that an error that is **not** handled by the chain is "upgraded" to a global uncaught exception and is thus considered by PHP to be fatal.

Since the error is converted to an exception before it is handled, the error handlers you define are used for both Exceptions and standard PHP errors. For example reusing the Error_Log and Error_Display classes we can define a chain that logs *all* errors and uncaught exceptions, suppresses non-fatal PHP errors or otherwise displays a friendly error message for fatal PHP errors/exceptions:

    <?php
    class Error_Suppress implements T_Exception_Handler_Action {
      function handle(Exception $e) {
        $fatal = E_ERROR|E_USER_ERROR|E_RECOVERABLE_ERROR;
        return ($e instanceof ErrorException) &&
               !($e->getSeverity()&$fatal);
      }
    }

    $env = new T_Environment_Http;
    $env->like('T_Exception_Handler')
            ->append(new Error_Log)
            ->append(new Error_Suppress)
            ->append(new Error_Display);
    ?>

In-Built Exception Handlers
---------------------------

For convenience, the library has already defined a few in-built exception sub-handlers that you can use straight away in your code:

* T_Exception_Handler_Debug implements the constructor-set error reporting level and displays any error then stops the script execution.
* T_Exception_Handler_Terminal implements the constructor-set error reporting level, then writes any error messages to the error stream before exiting with the specified error code.

    <?php
    // e.g. set up a debug HTTP environment
    $env = new T_Environment_Http;
    $env->like('T_Exception_Handler')
            ->append(new T_Exception_Handler_Debug(E_ALL|E_STRICT));

    // e.g. set a terminal environment
    $env = new T_Environment_Terminal;
    $env->like('T_Exception_Handler')
            ->append(new T_Exception_Handler_Terminal(E_ALL^E_NOTICE));
    ?>

There are currently no defined production error sub-handlers, although examples to form a basis for your own have already been covered.
