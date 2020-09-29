<?php
$form = $PMDR->get('Form');
$form->addFieldSet('information',array('legend'=>'Yelp Reviews Options'));
$form->addField('yelp_consumer_key','text',array('label'=>'Yelp Consumer Key', 'fieldset'=>'information'));
$form->addField('yelp_consumer_secret','text',array('label'=>'Yelp Consumer Secret', 'fieldset'=>'information'));
$form->addField('yelp_token','text',array('label'=>'Yelp Token', 'fieldset'=>'information'));
$form->addField('yelp_token_secret','text',array('label'=>'Yelp Token Secret', 'fieldset'=>'information'));
$form->addField('submit','submit');

$settings = $db->GetAssoc("SELECT varname, value FROM ".T_SETTINGS." WHERE grouptitle = 'yelp'");
$form->loadValues($settings);

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        foreach($data as $key=>$value) {
            $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array($value, $key));
        }

        $PMDR->addMessage('success','Successfully updated options.','update');
        redirect(URL);
    }
}
echo $form->toHTML();
?>