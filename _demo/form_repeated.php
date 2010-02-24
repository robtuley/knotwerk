<?php
define('DEMO','Repeatable Block Form');
require dirname(__FILE__).'/inc/header.php';
?>

<!--
jQuery script to hide/show optional extra repeatable blocks in the code. This
degrades for those without javascript by simply showing all the possible inputs
straight away.
  -->
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
    $('form li.repeated').each(function() {
		var $repeat = $(this);
		if ($('li.group',$repeat).slice($repeat.attr('min')).hide().length>0) {
			$repeat.append(
				$('<a href="#" class="add"></a>')
					.text($repeat.attr('title'))
					.click(function(){
						$('li.group:hidden:first',$repeat).show();
						if ($('li.group:hidden',$repeat).length==0) $(this).remove();
						return false;
					})
			);
		}
	});
});
// ]]>
</script>

<?php
// CREATE FORM
$form = new T_Form_Post('cv','Create CV');

$fieldset = new T_Form_Fieldset('details','Your Details');
$form->addChild($fieldset);

$fieldset->addChild(new T_Form_Text('name','Name'));

$email = new T_Form_Text('email','Email');
$email->attachFilter(new T_Validate_Email());
$fieldset->addChild($email);

$fieldset = new T_Form_Fieldset('prev_jobs','Your Previous Jobs');
$form->addChild($fieldset);

$repeated = new T_Form_Repeated('jobs','Add Another Job',1,6);
$repeated->addChild(new T_Form_Text('title','Title'));
$repeated->addChild(new T_Form_Text('company','Company'));
$start = new T_Form_Text('start','Start Date');
$start->setHelp('Enter date in format dd/mm/yyyy');
$start->setOptional()
      ->attachFilter(new T_Validate_UnixDate('d|m|y'));
$repeated->addChild($start);
$fieldset->addChild($repeated);

$skills = new T_Form_TextArea('skills','Additional Info');
$skills->setOptional()
       ->setHelp('Describe any additional career achievements, etc.');
$fieldset->addChild($skills);

$form->setForward($env->getRequestUrl());

// VALIDATE FORM
if ($env->isMethod('POST')) {
    $form->validate($env->input('POST'));
}

// ACTION FORM
if ($form->isPresent() && $form->isValid()) {

    $f = new T_Filter_Xhtml;

    // action (e.g. email, etc.)
    echo '<h2>CV for <a href="mailto:',
         $form->search('email')->getValue($f),'">',
         $form->search('name')->getValue($f),
         '</a></h2>';

    foreach ($form->search('jobs') as $job) {
        $date = $job->search('start');
        if ($date->isPresent()) {
            $date = ' (from '.date('d-m-Y',$date->getValue()).')';
        } else {
            $date = null;
        }
        echo '<p>',$job->search('title')->getValue($f),
             ', ',$job->search('company')->getValue($f),
             $date,'</p>';
    }

    $skills = $form->search('skills');
    if ($skills->isPresent()) {
        echo '<p>',$skills->getValue($f),'</p>';
    }
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