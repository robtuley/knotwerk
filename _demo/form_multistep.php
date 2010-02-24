<?php
define('DEMO','Multistep Form');
require dirname(__FILE__).'/inc/header.php';

// CREATE FORM
$form = new T_Form_MultiStep('insurance','Get a Quote');

// step 1

$step1 = new T_Form_Step('you','Contact Details');
$form->addChild($step1);

$fieldset = new T_Form_Fieldset('contact_details','Your Details');
$step1->addChild($fieldset);

$fieldset->addChild(new T_Form_Text('name','Name'));

$email = new T_Form_Text('email','Email');
$email->attachFilter(new T_Validate_Email());
$fieldset->addChild($email);

// step 2

$step2 = new T_Form_Step('your_history','Your History');
$form->addChild($step2);

$fieldset = new T_Form_Fieldset('driver_history','Driver History');
$step2->addChild($fieldset);

$comment = new T_Form_TextArea('penalty','Details of any driving penalties:');
$comment->setOptional();

$fieldset->addChild($comment);

// step 3

$step3 = new T_Form_Step('car','Your Car');
$form->addChild($step3);

$fieldset = new T_Form_Fieldset('car_details','Your Car');
$step3->addChild($fieldset);

$fieldset->addChild(new T_Form_Text('reg','Registration'));
$fieldset->addChild(new T_Form_Text('make','Make'));
$fieldset->addChild(new T_Form_Text('model','Model'));

$form->setForward($env->getRequestUrl());

// VALIDATE FORM
if ($env->isMethod('POST')) {
    $form->validate($env->input('POST'));
}

// ACTION FORM
if ($form->isPresent() && $form->isValid()) {

    // action..
    echo '<p>Thanks for requesting to get a quote, you details are:</p>';
    echo '<table><thead><tr><th>Description</th><th>Value</th></thead><tbody>';
    $data = array('Name'=>$form->search('name')->getValue(),
                  'Email'=>$form->search('email')->getValue(),
                  'History'=>$form->search('penalty')->getValue(),
                  'Registration'=>$form->search('reg')->getValue(),
                  'Model'=>$form->search('make')->getValue(),
                  'Make'=>$form->search('model')->getValue() );
    $f = new T_Filter_Xhtml;
    foreach ($data as $name => $val) {
        echo '<tr><td>',_transform($name,$f),'</td><td>',_transform($val,$f),'</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p><a href="'.$env->getRequestUrl()->getUrl($f).'">Try again &rsaquo;</a></p>';

} else {

    // render form
    $error  = new T_Form_XhtmlError();
    $render = new Demo_Form_Xhtml();
    $form->accept($error)
         ->accept($render);
    echo $error,$render;

}

require dirname(__FILE__).'/inc/footer.php';