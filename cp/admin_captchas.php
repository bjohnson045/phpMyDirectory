<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_captchas'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_captchas.tpl');

$captchas = array();
$captchas_directory = PMDROOT.'/modules/captcha/';
if(is_dir($captchas_directory)) {
    if ($dh = opendir($captchas_directory)) {
        while(($folder = readdir($dh)) !== false) {
            if(is_dir(PMDROOT.'/modules/captcha/'.$folder.'/') AND $folder != '.' AND $folder != '..') {
                $captchas[] = $folder;
            }
        }
        closedir($dh);
    }
}
sort($captchas);

$database_captchas = $db->GetCol("SELECT id FROM ".T_CAPTCHAS);

$install_captchas = array_diff($captchas,$database_captchas);

foreach($install_captchas AS $captcha) {
    $db->Execute("INSERT INTO ".T_CAPTCHAS." (id,settings) VALUES (?,'')",array($captcha));
}

if($uninstall_captchas = array_diff($database_captchas,$captchas)) {
    $db->Execute("DELETE FROM ".T_CAPTCHAS." WHERE id IN('".implode("','",$uninstall_captchas)."')");
}

$captchas = $db->GetAssoc("SELECT id, id AS name FROM ".T_CAPTCHAS." ORDER BY id ASC");
$form_enable = $PMDR->getNew('Form');
$form_enable->addField('captcha','select',array('options'=>$captchas));
$form_enable->addField('submit_enable','submit');

$template_content->set('title',$PMDR->getLanguage('admin_captchas'));

if($captcha = $db->GetRow("SELECT * FROM ".T_CAPTCHAS." WHERE id=?",array($PMDR->getConfig('captcha_type')))) {
    if(!file_exists(PMDROOT.'/modules/captcha/'.$captcha['id'].'/'.$captcha['id'].'_admin.php')) {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname='captcha_type'");
        trigger_error('Selected captcha '.$PMDR->getConfig('captcha_type').' is missing files.');
    }

    $form_enable->setFieldAttribute('captcha','value',$captcha['id']);

    $form = $PMDR->getNew('Form');
    $form->addFieldSet('details',array('legend'=>$captcha['id']));
    include(PMDROOT.'/modules/captcha/'.$captcha['id'].'/'.$captcha['id'].'_admin.php');
    $form->addField('submit','submit');

    $details = array();
    if(is_array(unserialize($captcha['settings']))) {
        $details = unserialize($captcha['settings']);
    }
    $form->loadValues(array_merge($details, $captcha));

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
            $db->Execute('UPDATE '.T_CAPTCHAS.' SET settings=? WHERE id=?',array($settings,$captcha['id']));
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($captcha['id'],$PMDR->getLanguage('admin_captchas'))),'update');
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
        $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='captcha_type'",array($_POST['captcha']));
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_POST['captcha'],$PMDR->getLanguage('admin_captchas'))),'update');
        redirect();
    }
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>