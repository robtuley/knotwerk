Provides an OO environment in which to build, validate and render HTTP forms. Forms are built as abstract containers specifying input types and validation rules, and can either be rendered using custom markup or by standard visitor renderers. Supports file uploads, hidden inputs, and automatic CSRF protection.

== Defining A Form ==

To use a form, you must first define it's structure. This is achieved by creating a parent form composite object, and adding all the elements that are required as children. The form object then encapsulates a specification for the user input that is required. For example, to build a skeleton enquiry form with name, email and message fields:

<?php
$form = new T_Form_Post('enquiry','Send Message');
$form->addChild(new T_Form_Text('name','Name'));
$form->addChild(new T_Form_Text('email','Email'));
$form->addChild(new T_Form_Text('subject','Subject'));
$form->addChild(new T_Form_TextArea('msg','Message'));
?>

The form object is not the only composite available, other composites like fieldsets can be used to build a tree that includes nested children elements. For example, if we introduced fieldset grouping to the above example:

<?php
$form = new T_Form_Post('enquiry','Send Message');

$set=new T_Form_Fieldset('contact','Contact Details')
$form->addChild($set);

$set->addChild(new T_Form_Text('name','Name'));
$set->addChild(new T_Form_Text('email','Email'));

$set=new T_Form_Fieldset('data','Your Enquiry')
$form->addChild($set);

$set->addChild(new T_Form_Text('subject','Subject'));
$set->addChild(new T_Form_TextArea('msg','Message'));
?>

=== Optional Fields ===

By default, elements are required rather than optional inputs, so in our enquiry form above all the elements defined must be submitted for the form to validate. To create an optional input, use the setOptional() method on the element. e.g. to make message subject an optional input:

<?php
// ... [snip] ...
$subject = new T_Form_Text('subject','Subject');
$subject->setOptional();
$set->addChild($subject);
// ... [snip] ...
?>

=== Adding Validation Rules ===

In our current enquiry form we have defined that certain inputs must be available, but we have not yet defined any rules that this input data must meet. We do this by attaching input validation filters to the elements. Some standard filters are available in the library as the T_Validate_* family of classes.

<?php
$email = new T_Form_Text('email','Email');
$email->attachFilter(new T_Validate_Email);
?>

=== Custom Validation Rules ===

There are a number of standard validation filters defined within the library itself for convenience, but it is easy to define your own validation rules. Validators are written as T_Filter classes, and failures should result in a T_Exception_Filter being thrown. For example, a custom validation class based on a particular regex might look like:

<?php
class MyCustomValidator implements T_Filter
{
    function transform($value)
    {
        $regex = '/some regex/'
        if (!preg_match($regex,$value)) {
            throw new T_Exception_Filter('must be in the format XXXX');
        }
        return $value;
    }
}
?>

=== Filters Are Not Just For Validation! ===

Filters act on the data submitted and although they can act as validators by throwing T_Exception_Filter exceptions, they can also/instead transform the input data that is submitted. For example to make sure the email address submitted is not only valid, but also to transform it to lower case:

<?php
$email = new T_Form_Text('email','Email');
$email->attachFilter(new T_Validate_Email)
      ->attachFilter('mb_strtolower');
?>

=== Element Attributes ===

There are set and get methods for additional element attributes, for example it can be used to set ID values for javascript to target, or to set textarea dimensions:

<?php
$msg = new T_Form_TextArea('msg','Your Message');
$msg->setAttribute('rows',7)
    ->setAttribute('cols',60);
?>

Setting some attributes can also add validation filters internally. For example setting the maxlength attribute on a text field also adds a validation filter to enforce the max length restriction server-side.

<?php
$name = new T_Form_Text('name','Name');
$name->setAttribute('maxlength',100);
?>

=== Selects, Radios and Checkboxes ===

List elements such as selects (T_Form_Select), radio options (T_Form_Radio) or checkboxes (T_Form_Checkbox) have the same interface as the text elements we have already seen with the addition of a setOptions() method that is used to set the available options. The first argument to this method is an array of value=>name pairs, and an optional second argument is the not present option.

<?php
$title = new T_Form_Select('title','Title');
$options = array('Mr','Mrs','Ms','Miss','Dr');
$title->setOptions($options,'Select your title');
?>

While both select and radio elements encapsulate a scalar response, the checkbox element encapsulates an array of selected elements from the options provided. All these elements check that the response is from the list of options provided during validation.

=== Setting Element Defaults ===

Element defaults can be set using the setDefault() method of any element. For list elements (select, radio, checkbox), the default(s) must be in the previously set list of options.

<?php
$name = new T_Form_Text('name','Name');
$name->setDefault('Joe Bloggs');

$lang = new T_Form_Checkbox('lang','Your Languages');
$options = array('php'=>'PHP','py'=>'Python','rails'=>'RoR');
$lang->setOptions($options)
     ->setDefault(array('php','py'));
?>

=== File Uploads ===

File uploads can be included in a form using the T_Form_Upload element. By default any file can be uploaded, and a T_File_Upload object is returned once the element is validated. To restrict what files can be uploaded, we use validation filters in a similar way to other elements. For example, the T_Validate_UploadMime filter can be used to restrict the file types that can be uploaded.

<?php
$doc = new T_Form_Upload('doc','Document');
$permitted = array('doc','docx','txt','xls','xlsx');
$upload->attachFilter(new T_Validate_UploadMime($permitted));
?>

Often uploaded files are images, and a number of validation filters specifically exists for this purpose. For example, an image upload that must be an image (PNG, GIF or JPEG permitted) which is at least 200px wide and no wider than 4000px:

<?php
$img = new T_Form_Upload('img','Image');
$img->attachFilter(new T_Validate_ImageGdUpload)
    ->attachFilter(new T_Validate_ImageWidthRange(200,4000));
?>

=== Hidden Elements ===

Hidden values can be included in forms using the T_Form_Hidden element. This element includes the specified value in the form, along with a hashed checksum that guarantees that the elemnt will only validate on submission if the origianl value has not been tampered with.

<?php
$timeout = new T_Form_Hidden('timeout',time()+15*60);
?>

== Rendering a Form ==

A form composite encapsulates the business logic of a user input -- what is required, and what rules it must obey -- but has no knowledge as to how the form might be rendered. Instead, it provides an accept() method to pass in a visitor that does encapsulate such logic. By default, a XHTML form renderer is provided:

<?php
$form = new T_Form_Post('enquiry','Send Message');
$form->addChild(new T_Form_Text('name','Name'));
$form->addChild(new T_Form_Text('email','Email'));

$xhtml = new T_Form_Xhtml;
$form->accept($xhtml);
echo $xhtml;
?>

The T_Form_Xhtml class is designed to be extended into your own form renderer for your application, and provides various hooks to insert HTML content around the elements. For example, a form renderer that displays a form with elements in an ordered list can be achieved with:

<?php
class MyFormView extends T_Form_Xhtml
{
    protected function preGroup($node)
    {
        $this->changeIndent(1);
        return $this->indent.'<ol>'.EOL;
    }
    protected function preLabel($node)
    {
        $this->changeIndent(1);
        $xhtml = $this->indent.'<li>'.EOL;
        $this->changeIndent(1);
        return $xhtml;
    }
    protected function postElement($node)
    {
        $this->changeIndent(-1);
        $xhtml .= $this->indent.'</li>'.EOL;
        $this->changeIndent(-1);
        return $xhtml;
    }
    protected function preNestedFieldset($node)
    {
        $this->changeIndent(1);
        $xhtml = $this->indent.'<li>'.EOL;
        $this->changeIndent(1);
        return $xhtml;
    }
    protected function postNestedFieldset($node)
    {
        $this->changeIndent(-1);
        $xhtml = $this->indent.'</li>'.EOL;
        $this->changeIndent(-1);
        return $xhtml;
    }
    protected function postGroup($node)
    {
        $xhtml = $this->indent.'</ol>'.EOL;
        $this->changeIndent(-1);
        return $xhtml;
    }
}
?>

Separate renderers can perform different rendering tasks on a form. For example T_Form_XhtmlError simply renders a list of the form errors that could be placed at the top of an HTML page. These visitors provide a general way for you to easily write re-usable form rendering rules, which can be used on the whole form, or partial parts of it (depending what you call accept on).

== Performing Form Validation ==

Validating the form or parts of the form is achieved using the isSubmitted() and validate() methods. Both of these functions take the data to validate as an argument in the form of a T_Cage_Array. T_Cage_Array user input objects can be [/how-to/user-input retrieved from the code environment] or built directly from the superglobals:

<?php
$env = new T_Environment_Http;
$post = $env->input('POST');
// ... OR ...
if (get_magic_quotes_gpc()) {
    $f = new T_Filter_NoMagicQuotes;
    $post = new T_Cage_Array($f->transform($_POST));
} else {
    $post = new T_Cage_Array($_POST);
}
?>

Forms are validated using the validate() method and this executes the validation assuming the form has been submitted. To check whether a form has been submitted (if you have multiple forms on the same page), the isSubmitted() method can be used. Once validated the methods isPresent() and isValid() can be used to check if a submission if present and valid.

<?php
$env = new T_Environment_Http;
$form = new T_Form_Post(/* snip */);
// ... build form ...

$post = $env->input('POST');
if ($env->isMethod('POST') && $form->isSubmitted($post)) {
    $form->validate($post);
}
if ($form->isPresent() && $form->isValid()) {
    // action form..
} else {
    // display form
}
?>

Note that until a form **fails** validation it is regarded as being in a valid state (so isValid() returns true). Thus to check whether a form has been submitted and is valid you must make sure both that the form isPresent() and isValid().

== Using Form Results ==

Specific elements can be retrieved from the form composite via their alias using the search() method. Elements also support the isPresent() and isValid() method individually and getValue() can be used to retrieve the filtered value.

<?php
// .. build form, and validate ..

// retrieve and action results
$email = $form->search('email')->getValue();
$tel = $form->search('tel');
$tel = $tel->isPresent() ? $tel->getValue() : 'none';
// ... etc.
?>

== Delving Deeper... ==

Shortly, there will be some How-Tos posted about:

* How to use forms with controllers
* Avoiding CSRF vunerabilities
* Building repeatable form blocks and multi-step forms.
