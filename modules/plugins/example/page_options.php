<?php

// Create Form
$form = $PMDR->get('Form');
$form->addFieldSet('information',array('legend'=>'Plugin Options'));

$form->addField('plugin_example_admin_notice','checkbox',array('label'=>'Plugin Warning', 'fieldset'=>'information'));
$form->addFieldNote('plugin_example_admin_notice', 'Creates the warning prompt on the <a href="'.BASE_URL_ADMIN.'">Admin Index</a> page.');

$form->addField('plugin_example_user_message','checkbox',array('label'=>'User Index Message', 'fieldset'=>'information'));
$form->addFieldNote('plugin_example_user_message', 'Displays a message on the <a href="'.BASE_URL.MEMBERS_FOLDER.'">Members Index</a> page.');

$form->addField('plugin_example_breadcrumbs_date','checkbox',array('label'=>'Prepend Date in Header', 'fieldset'=>'information'));
$form->addFieldNote('plugin_example_breadcrumbs_date', 'Prepends the date on <a href="'.BASE_URL.'">any non-admin page</a> in the top left corner.');

$form->addField('plugin_example_category_count','checkbox',array('label'=>'Category Count', 'fieldset'=>'information'));
$form->addFieldNote('plugin_example_category_count', 'Adds the listing count to a <a href="'.BASE_URL.'/browse_categories.php">category page</a> (must be in a category, not just the top root as linked.)');

$form->addField('submit','submit');

// Load Options
$data = $db->GetAll('SELECT `varname`, `value` FROM '.T_SETTINGS.' WHERE `grouptitle` = \'plugin_example\';');

// Load Options into Form
$options = array();
foreach($data as $value) {
    $options[$value['varname']] = $value['value'];
}
$form->loadValues($options);

// Check Submission
if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
	} else {

		// Updates each setting option in the form.
		foreach($data as $key=>$value) {
			$db->Execute('UPDATE '.T_SETTINGS.' SET `value` = ? WHERE `varname` = ?;', array($value, $key));
		}

        $PMDR->addMessage('success','Successfully updated options.','update');

		// In case the options submenu is moved, or plugin is renamed, we will use the GET variables.
        //redirect(array('id' => 'example', 'submenu' => '2'));
        redirect(array('id' => $_GET['id'], 'submenu' => $_GET['submenu']));
    }
}

echo $form->toHTML();
?>