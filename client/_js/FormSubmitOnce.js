/**
 * Disables form buttons when a user submits a form.
 *
 * This javascript template disables form buttons after a user clicks on them
 * to submit a form. It can be used to prevent double form submissions.
 *
 * @requires jquery.js
 */
var FormSubmitOnce = function()
{

	/**
	 * Initialises the submit button prevention.
	 */
    function init()
    {
        $('form button').click(buttonClick);
        $('form input[type=submit]').click(inputClick);
           // use click handlers rather than the form submit event as 
           // if there are multiple buttons we don't know which ones were
           // clicked!
    }

    function inputClick()
    {
    	copyToHidden($(this)); // copy value before modifying it!
        $(this).val('Processing..').attr( {'disabled':'disabled'} );
        $(this).parents('form').submit();
        return false;
    }

    function buttonClick()
    {
        $(this).text('Processing..').attr( {'disabled':'disabled'} );
        copyToHidden($(this));
        $(this).parents('form').submit();
        return false;
    }

	/**
	 * Copies an element's name and value.<b> 
	 *
	 * This copies element name and value into a hidden form element 
	 * inserted after the element. This is necessary as disabling the 
	 * button/input element actually prevents the value submission upon
	 * which the server may depend.
	 */
    function copyToHidden(element)
    {
        var hidden = $('<input type="hidden" />');
     	hidden.val(element.val());
     	hidden.attr('name',element.attr('name'));
     	element.after(hidden);
    }

    return { init:init }

}();
 
$(document).ready(FormSubmitOnce.init);


