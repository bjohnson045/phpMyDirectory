<?php
define('UPGRADE',true);

include('../../defaults.php');
include ('../includes/functions.php');

$PMDR->loadLanguage(array());

$version = $db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='pmd_version'");
if(count(explode('.',$version)) != 3) {
    $version .= '.0';
}

$upgrade_version = include(PMDROOT.'/includes/version.php');

if(!isset($_GET['action'])) {
    $template_content = $PMDR->getNew('Template',PMDROOT.'/install/template/upgrade_index.tpl');
    $template_content->set('upgrade_version',$upgrade_version);

    if(!validPHPVersion('5.6')) {
        $template_content->set('upgrade_php',true);
    }

    if(!validionCube('5.0')) {
        $template_content->set('upgrade_ioncube',true);
    }

    if(!file_exists(PMDROOT.'/install/upgrade/'.str_replace('.','-',$version).'.php')) {
        $template_content->set('latest_version',true);
    }

    if(!is_writable(PMDROOT.'/cache')) {
        $template_content->set('cache',true);
    }

    $form = $PMDR->get('Form');
    $form->addFieldSet('upgrade',array('legend'=>''));
    $form->addField('login','text',array('label'=>'Administrator Username','fieldset'=>'upgrade'));
    $form->addField('pass','text',array('label'=>'Administrator Password','fieldset'=>'upgrade'));
    $form->addField('submit','submit',array('label'=>'Continue','fieldset'=>'submit'));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if(!$user = $db->GetRow("SELECT * FROM ".T_USERS." WHERE (login=? OR user_email=?)",array($data['login'],$data['login']))) {
            $form->addError('Login failed.');
        } elseif(isset($user['password_hash'])) {
            if($user['pass'] != hash($user['password_hash'],$data['pass'].$user['password_salt'])) {
                $form->addError('Login failed.');
            }
        } else {
            if($user['pass'] != md5($data['pass'])) {
                $form->addError('Login failed.');
            }
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $_SESSION['login'] = $data['login'];
            $_SESSION['pass'] = md5($data['pass']);
            $_SESSION['import_hash'] = md5($data['login'].md5($data['pass']));
            $_SESSION['version'] = $version;
            if(version_compare($version,'1.5.0','<')) {
                redirect(BASE_URL.'/install/upgrade/index.php',array('action'=>'options'));
            } else {
                redirect(BASE_URL.'/install/upgrade/upgrade.php');
            }
        }
    }
} else {
    $fields = $db->GetAssoc("SELECT id, name FROM ".T_FIELDS);

    if(!$fields) {
        redirect(BASE_URL.'/install/upgrade/upgrade.php');
    }

    $template_content = $PMDR->getNew('Template',PMDROOT.'/install/template/upgrade_options.tpl');
    $form = $PMDR->get('Form');
    $form->addFieldSet('upgrade',array('legend'=>'Upgrade Options'));
    $help_text = '';
    if(version_compare($version,'1.1.3','<')) {
        $help_text .= '<p>A built in phone number field was added to listings.  In the past this was usually handled by a custom field added in the admin area
        under Tools->Field Editor.  The below form lets you select a field to automatically import a custom field into the new phone number field.  If you do not have
        a phone number custom field simply set the selection to "None"</p>';

        $form->addField('field_phone','select',array('label'=>'Phone Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
    }
    if(version_compare($version,'1.1.8','<')) {
        $help_text .= '<p>A built in fax number field was added to listings.  In the past this was usually handled by a custom field added in the admin area
        under Tools->Field Editor.  The below form lets you select a field to automatically import a custom field into the new fax number field.  If you do not have
        a fax number custom field simply set the selection to "None"</p>';

        $form->addField('field_fax','select',array('label'=>'Fax Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
    }
    if(version_compare($version,'1.5.0','<')) {
        $help_text .= '<p>Built in fields have been added for social links.  In the past these were usually handled by custom fields added in the admin area
        under Tools->Field Editor.  The below form lets you select a field to automatically import a custom field into the new social fields.  If you do not have
        any social link custom field simply set the selections to "None"</p>';

        $form->addField('facebook_page_id','select',array('label'=>'Facebook Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('twitter_id','select',array('label'=>'Twitter Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('google_page_id','select',array('label'=>'Google Page Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('linkedin_id','select',array('label'=>'Linkedin Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('linkedin_company_id','select',array('label'=>'Linkedin Company Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('pinterest_id','select',array('label'=>'Pinterest Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('youtube_id','select',array('label'=>'Youtube Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('foursquare_id','select',array('label'=>'Foursquare Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
        $form->addField('instagram_id','select',array('label'=>'Instagram Field','fieldset'=>'upgrade','first_option'=>'None','options'=>$fields));
    }
    $form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));
    $template_content->set('help_text',$help_text);

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $session_fields = array(
                'field_phone',
                'field_fax',
                'facebook_page_id',
                'twitter_id',
                'google_page_id',
                'linkedin_id',
                'linkedin_company_id',
                'pinterest_id',
                'youtube_id',
                'foursquare_id'
            );

            foreach($session_fields AS $field) {
                if($data[$field] != '' AND is_numeric($data[$field])) {
                    $_SESSION[$field] = $data[$field];
                }
            }

            redirect(BASE_URL.'/install/upgrade/upgrade.php');
        }
    }
}
$template_content->set('content',$form->toHTML());

include(PMDROOT.'/install/includes/template_setup.php');
?>