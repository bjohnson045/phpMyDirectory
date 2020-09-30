<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_email_marketing'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_email_marketing.tpl');

if(isset($_GET['sync_lists'])) {
    if($email_marketing = $PMDR->get('Email_Marketing') AND $lists = $email_marketing->getLists(true)) {
        $PMDR->addMessage('success','Lists synced.');
    } else {
        $PMDR->addMessage('error','There was a problem syncing the lists.  Please verify the API key is correct.');
    }
    redirect();
}

$marketers = array();
$marketers_directory = PMDROOT.'/modules/email_marketing/';
if(is_dir($marketers_directory)) {
    if ($dh = opendir($marketers_directory)) {
        while(($folder = readdir($dh)) !== false) {
            if(is_dir(PMDROOT.'/modules/email_marketing/'.$folder.'/') AND $folder != '.' AND $folder != '..') {
                $marketers[] = $folder;
            }
        }
        closedir($dh);
    }
}
sort($marketers);

$database_marketers = $db->GetCol("SELECT id FROM ".T_EMAIL_MARKETING);

$install_marketer = array_diff($marketers,$database_marketers);

foreach($install_marketer AS $marketer) {
    $db->Execute("INSERT INTO ".T_EMAIL_MARKETING." (id,settings) VALUES (?,'')",array($marketer));
}

if($uninstall_marketers = array_diff($database_marketers,$marketers)) {
    $db->Execute("DELETE FROM ".T_EMAIL_MARKETING." WHERE id IN('".implode("','",$uninstall_marketers)."')");
}

$marketers = $db->GetAssoc("SELECT id, id AS name FROM ".T_EMAIL_MARKETING." ORDER BY id ASC");
$form_enable = $PMDR->getNew('Form');
$form_enable->addField('email_marketing','select',array('label'=>$PMDR->getLanguage('admin_email_marketing_service'),'first_option'=>array(''=>'None'),'options'=>$marketers));
$form_enable->addField('submit_enable','submit');

$template_content->set('title',$PMDR->getLanguage('admin_email_marketing'));
if($PMDR->getConfig('email_marketing') AND $marketer = $db->GetRow("SELECT * FROM ".T_EMAIL_MARKETING." WHERE id=?",array($PMDR->getConfig('email_marketing')))) {
    if(!file_exists(PMDROOT.'/modules/email_marketing/'.$marketer['id'].'/'.$marketer['id'].'_admin.php')) {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname='email_marketing'");
        trigger_error('Selected Email Marketer '.$PMDR->getConfig('email_marketing').' is missing files.');
        redirect(BASE_URL_ADMIN.'/admin_email_marketing.php');
    }

    $form_enable->setFieldAttribute('email_marketing','value',$marketer['id']);

    $details = array();
    if(is_array(unserialize($marketer['settings']))) {
        $details = unserialize($marketer['settings']);
    }

    $cache = unserialize($marketer['cache']);
    if(isset($cache['lists'])) {
        $template_content->set('current_lists',$cache['lists']);
    }

    $template_content->set('current_marketer',$marketer['id']);

    $form = $PMDR->getNew('Form');
    $form->addFieldSet('details');
    include(PMDROOT.'/modules/email_marketing/'.$marketer['id'].'/'.$marketer['id'].'_admin.php');
    $form->addField('submit','submit');

    $form->loadValues(array_merge($details, $marketer));

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
            $db->Execute('UPDATE '.T_EMAIL_MARKETING.' SET settings=? WHERE id=?',array($settings,$marketer['id']));
            $PMDR->get('Email_Marketing')->getLists();
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($marketer['id'],$PMDR->getLanguage('admin_email_marketing'))),'update');
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
        if(empty($_POST['email_marketing']) AND $email_marketing = $PMDR->get('Email_Marketing')) {
            $email_marketing->unlinkLists();
        }
        $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='email_marketing'",array($_POST['email_marketing']));
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_POST['email_marketing'],$PMDR->getLanguage('admin_email_marketing'))),'update');
        redirect();
    }
} else {
    $PMDR->addMessage('warning',$PMDR->getLanguage('admin_email_marketing_notice'));
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>