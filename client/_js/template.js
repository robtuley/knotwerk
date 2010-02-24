/**
 * This file is a template of the MODULE pattern in JS.
 *
 * @see http://icant.co.uk/articles/seven-rules-of-unobtrusive-javascript/
 */

var myScript = function()
{
    /* define methods and functions */
    var an_attribute = 'value';

    function init()
    {
        reset(); /* reference other functions internally */
    }

    function show()
    {
        // do stuff
    }

    function reset()
    {
        // do stuff
    }

    var foo = 'bar';

    function baz()
    {
        // do stuff
    }

    return {
             baz:baz, /* reveal foo and baz as public methods/attributes */
             foo:foo
           }

}();