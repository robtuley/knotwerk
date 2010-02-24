<?php
define('DEMO','Simple Enquiry Form');
require dirname(__FILE__).'/inc/header.php';

// CREATE FORM
$form = new T_Form_Post('contact','Send Enquiry');

$fieldset = new T_Form_Fieldset('details','Your Details');
$form->addChild($fieldset);

$fieldset->addChild(new T_Form_Text('name','Name'));

$email = new T_Form_Text('email','Email');
$email->attachFilter(new T_Validate_Email());
$fieldset->addChild($email);

$fieldset = new T_Form_Fieldset('enquiry','Your Question');
$form->addChild($fieldset);

$comment = new T_Form_TextArea('msg','Message');
$fieldset->addChild($comment);

$form->setForward($env->getRequestUrl());

// VALIDATE FORM
if ($env->isMethod('POST')) {
    $form->validate($env->input('POST'));
}

// ACTION FORM
if ($form->isPresent() && $form->isValid()) {

    // action (e.g. email, etc.)
    echo '<p>Thanks for your submission <a href="mailto:',
         $form->search('email')->getValue(new T_Filter_Xhtml()),
         '">',$form->search('name')->getValue(new T_Filter_Xhtml()),
         '</a>!</p>';

} else {

    // render form
    $error  = new T_Form_XhtmlError();
    $render = new Demo_Form_Xhtml();
    $form->accept($error)
         ->accept($render);
    echo $error,$render;

}

require dirname(__FILE__).'/inc/footer.php';