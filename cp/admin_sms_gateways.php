<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_sms_gateways'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_sms_gateways.tpl');

$gateways = array();
$gateways_directory = PMDROOT.'/modules/sms/';
if(is_dir($gateways_directory)) {
    if ($dh = opendir($gateways_directory)) {
        while(($folder = readdir($dh)) !== false) {
            if(is_dir(PMDROOT.'/modules/sms/'.$folder.'/') AND $folder != '.' AND $folder != '..') {
                $gateways[] = $folder;
            }
        }
        closedir($dh);
    }
}
sort($gateways);

$database_gateways = $db->GetCol("SELECT id FROM ".T_SMS_GATEWAYS);

$install_gateways = array_diff($gateways,$database_gateways);

foreach($install_gateways AS $gateway) {
    $db->Execute("INSERT INTO ".T_SMS_GATEWAYS." (id,settings) VALUES (?,'')",array($gateway));
}

if($uninstall_gateways = array_diff($database_gateways,$gateways)) {
    $db->Execute("DELETE FROM ".T_SMS_GATEWAYS." WHERE id IN('".implode("','",$uninstall_gateways)."')");
}

$gateways = $db->GetAssoc("SELECT id, id AS name FROM ".T_SMS_GATEWAYS." ORDER BY id ASC");
$form_enable = $PMDR->getNew('Form');
$form_enable->addField('gateway','select',array('first_option'=>array(''=>'None'),'options'=>$gateways));
$form_enable->addField('submit_enable','submit');

$template_content->set('title',$PMDR->getLanguage('admin_sms_gateways'));

if($processor = $db->GetRow("SELECT * FROM ".T_SMS_GATEWAYS." WHERE id=?",array($PMDR->getConfig('sms_gateway')))) {
    if(!file_exists(PMDROOT.'/modules/sms/'.$processor['id'].'/'.$processor['id'].'_admin.php')) {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname='sms_gateway'");
        trigger_error('Selected SMS gateway '.$PMDR->getConfig('sms_gateway').' is missing files.');
        redirect(BASE_URL.'/admin_sms_gateways.php');
    }

    $form_enable->setFieldAttribute('gateway','value',$processor['id']);

    $form = $PMDR->getNew('Form');
    $form->addFieldSet('details',array('legend'=>$processor['id']));
    include(PMDROOT.'/modules/sms/'.$processor['id'].'/'.$processor['id'].'_admin.php');
    $form->addField('submit','submit');

    $details = array();
    if(is_array(unserialize($processor['settings']))) {
        $details = unserialize($processor['settings']);
    }
    $form->loadValues(array_merge($details, $processor));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            foreach($form->elements AS $name=>$element) {
                if(!in_array($name,array('submit'))) {
                    $settings[$name] = $data[$name];
                }
            }
            $settings = serialize($settings);
            $db->Execute('UPDATE '.T_SMS_GATEWAYS.' SET settings=? WHERE id=?',array($settings,$processor['id']));
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($processor['id'],$PMDR->getLanguage('admin_sms_gateways'))),'update');
            redirect();
        }
    }

    $template_content->set('content',$form->toHTML());
}

$template_content->set('form',$form_enable);

if($form_enable->wasSubmitted('submit_enable')) {
    $data = $form_enable->loadValues();
    if(!$form_enable->validate()) {
        $PMDR->addMessage('error',$form_enable->parseErrorsForTemplate());
    } else {
        $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='sms_gateway'",array($_POST['gateway']));
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_POST['gateway'],$PMDR->getLanguage('admin_sms_gateways'))),'update');
        redirect();
    }
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>