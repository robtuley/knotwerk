Accessing User Input
====================

User input is normally handled in PHP scripts using the $_GET, $_POST, $_COOKIE, etc superglobals. Once the library is bootstrapped, these superglobals remain unaffected and can be manipulated as normal. If you choose to use the library [/how-to/environments code environments], an alternative route to access user input is also available.

GET and POST Variables
----------------------

The GET and POST data is available through the environment input() method as caged arrays (see T_Cage_Array). When retrieving array keys the interface forces the user to specify what you are expecting (a scalar or array), and provides an optional filter method to validate the input.

    <?php
    $env = new T_Environment_Http;

    $page = 1;
    // either ...
    if (isset($_GET['page'])) $page = (int) $_GET['page'];
    // ... or ...
    if ($env->input('GET')->exists('page')) {
        try {
            $page = $env->input('GET')
                        ->asScalar('page')
                        ->filter(new T_Validate_Int)
                        ->uncage();
        } catch (Exception $e) { $page = 1; }
    }
    ?>

In the example above, the 'page' GET variable is validated as a scalar integer. If it is an array or not an integer an exception will be thrown. In most cases this can be left to bubble up to the global error handler, or it can be explicitally handled with a try-catch block. The example above is trivial and the normal $_GET code is shorter and cleaner (although note it is not entirely safe, an error will occur if the input is an array). However, once things become more complicated the cage interface actually helps:

    <?php
    $choices = array();  // expect array of integers via POST
                         // e.g. $choices = $_POST['choices'];
    if ($env->input('POST')->exists('choices')) {
        $f = new T_Filter_ArrayMap(new T_Validate_Int);
        $choices = $env->input('POST')
                       ->asArray('choice')
                       ->filter($f)
                       ->uncage();
    }
    ?>

Note that the filter method can be called multiple times to execute multiple filters against the input (these can validate or simply manipulate the data), or the filter method does not need to be called at all. The asArray or asScalar methods are called with the key name as an argument to retrieve that element, and the uncage method is always called finally to retrieve the filtered data from the "cage".

Cookies
-------

Cookie data can be **retrieved** in a similar manner to the GET or POST data via the environment input() method, and is uncaged in the same way: the example below shows how you could use a PHP5.3 lambda function to validate your input.

    <?php
    $theme = 'default';
    if ($env->input('COOKIE')->exists('theme')) {
        $fn = function ($th) {
            if (!/* theme is installed */) $th='default';
            return $th;
        };
        $theme = $env->input('COOKIE')
                     ->asScalar('theme')
                     ->filter($fn)
                     ->uncage();
    }
    ?>

To manage the cookies, the Cookie cage has a set() and delete() method.

    <?php
    // set a new theme
    $env->input('COOKIE')
        ->set('theme','newThemeName',time()+30*24*60*60);
                          // expires in 30 days ^
    // delete new theme
    $env->input('COOKIE')->delete('theme');
    ?>

The set method supports the same argument list to the inbuilt setcookie function, but default values for path and domain that are taken from the environment app root URL if it is available.