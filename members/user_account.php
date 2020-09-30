<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->loadLanguage(array('user_account','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_general_edit_account'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_account.php','text'=>$PMDR->getLanguage('user_account_details')));

if(isset($_GET['update_required']) AND $_GET['update_required'] == 'true') {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_account_update_required'));
}

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));
$user['email_lists'] = $db->GetCol("SELECT list_id FROM ".T_EMAIL_LISTS_LOOKUP." WHERE user_id=?",array($user['id']));
$user['login_providers'] = implode(', ',$db->GetCol("SELECT login_provider FROM ".T_USERS_LOGIN_PROVIDERS." WHERE user_id=?",array($user['id'])));

/** @var Form */
$form = $PMDR->getNew('Form');
$form->enctype = 'multipart/form-data';
$form->addFieldSet('user_details',array('legend'=>$PMDR->getLanguage('user_account_details')));
$form->addFieldSet('address',array('legend'=>$PMDR->getLanguage('user_account_address')));
$form->addFieldSet('notifications',array('legend'=>$PMDR->getLanguage('user_account_notifications')));
$form->addField('id','custom',array('label'=>$PMDR->getLanguage('user_account_user_id'),'fieldset'=>'user_details'));
if(!$PMDR->getConfig('login_module') OR empty($user['login_provider']) OR empty($user['login']) OR strstr($user['login'],'RemoteLogin')) {
    $form->addField('login','text',array('label'=>$PMDR->getLanguage('user_account_username'),'fieldset'=>'user_details'));
    $form->addValidator('login', new Validate_Username());
} else {
    $form->addField('login','custom',array('label'=>$PMDR->getLanguage('user_account_username'),'fieldset'=>'user_details'));
}
if(!$PMDR->getConfig('login_module') OR empty($user['user_email'])) {
    $form->addField('user_email','text',array('label'=>$PMDR->getLanguage('user_account_email'),'fieldset'=>'user_details'));
    $form->addValidator('user_email', new Validate_Email());
    $form->addValidator('user_email', new Validate_NonEmpty());
} else {
    $remote_emails = $db->GetAssoc("SELECT email AS email_key, email FROM ".T_USERS_LOGIN_PROVIDERS." WHERE user_id=? AND email!=''",array($user['id']));
    if(count($remote_emails) < 2) {
        $form->addField('user_email','custom',array('label'=>$PMDR->getLanguage('user_account_email'),'fieldset'=>'user_details'));
    } else {
        // Merge the current account email for upgrade purposes as we do not store it
        $remote_emails[$user['user_email']] = $user['user_email'];
        $form->addField('user_email','select',array('label'=>$PMDR->getLanguage('user_account_email'),'fieldset'=>'user_details','options'=>$remote_emails));
    }
}
if($PMDR->getConfig('user_display_name')) {
    $form->addField('display_name','text',array('label'=>$PMDR->getLanguage('user_account_display_name'),'fieldset'=>'user_details'));
    $form->addValidator('display_name', new Validate_NonEmpty());
}
$form->addField('user_first_name','text',array('label'=>$PMDR->getLanguage('user_account_first_name'),'fieldset'=>'user_details'));
$form->addField('user_last_name','text',array('label'=>$PMDR->getLanguage('user_account_last_name'),'fieldset'=>'user_details'));
$form->addField('user_organization','text',array('label'=>$PMDR->getLanguage('user_account_organization'),'fieldset'=>'user_details'));
$form->addField('profile_image','file',array('label'=>$PMDR->getLanguage('user_account_profile_picture'),'fieldset'=>'user_details'));
if($image_url = get_file_url(PROFILE_IMAGES_PATH.$PMDR->get('Session')->get('user_id').'.*')) {
    $form->addField('current_profile_image','custom',array('label'=>$PMDR->getLanguage('user_account_profile_current_image'),'fieldset'=>'user_details','html'=>'<img src="'.$image_url.'">'));
    $form->addField('delete_profile_image','checkbox',array('label'=>$PMDR->getLanguage('user_account_profile_picture_delete'),'fieldset'=>'user_details','value'=>'0'));
}
$fields = $PMDR->get('Fields')->addToForm($form,'users',array('fieldset'=>'user_details','editable'=>true,'admin_only'=>false));
if($email_lists = $db->GetAssoc("SELECT id, title FROM ".T_EMAIL_LISTS." WHERE hidden=0")) {
    $form->addField('email_lists','checkbox',array('fieldset'=>'user_details','value'=>$db->GetCol("SELECT id FROM ".T_EMAIL_LISTS." WHERE hidden=0 AND optout=1"),'options'=>$email_lists));
}
unset($email_lists);
$form->addField('timezone','select',array('label'=>$PMDR->getLanguage('user_account_timezone'),'fieldset'=>'user_details','first_option'=>'','options'=>include(PMDROOT.'/includes/timezones.php')));
$form->addField('user_address1','text',array('label'=>$PMDR->getLanguage('user_account_address1'),'fieldset'=>'address'));
$form->addField('user_address2','text',array('label'=>$PMDR->getLanguage('user_account_address2'),'fieldset'=>'address'));
$form->addField('user_city','text',array('label'=>$PMDR->getLanguage('user_account_city'),'fieldset'=>'address'));
$form->addField('user_state','text',array('label'=>$PMDR->getLanguage('user_account_state'),'fieldset'=>'address'));
$form->addField('user_country','select',array('label'=>$PMDR->getLanguage('user_account_country'),'fieldset'=>'address','first_option'=>'','options'=>get_countries_array()));
$form->addField('user_zip','text',array('label'=>$PMDR->getLanguage('user_account_zipcode'),'fieldset'=>'address'));
$form->addField('user_phone','text',array('label'=>$PMDR->getLanguage('user_account_phone'),'fieldset'=>'address'));
$form->addField('user_fax','text',array('label'=>$PMDR->getLanguage('user_account_fax'),'fieldset'=>'address'));
$form->addField('favorites_notify','checkbox',array('fieldset'=>'notifications'));

if(!$PMDR->getConfig('login_module') OR empty($user['login_provider'])) {
    $form->addFieldSet('password_change',array('legend'=>$PMDR->getLanguage('user_account_password_change')));
    $form->addField('pass','password',array('label'=>$PMDR->getLanguage('user_account_password'),'fieldset'=>'password_change'));
    $form->addField('pass_new','password',array('label'=>$PMDR->getLanguage('user_account_password_new'),'fieldset'=>'password_change','strength'=>true,'strength_label'=>$PMDR->getLanguage('user_account_strength')));
    $form->addField('pass_new_confirm','password',array('label'=>$PMDR->getLanguage('user_account_password_confirm'),'fieldset'=>'password_change'));
    $form->addValidator('pass_new', new Validate_Password(false));
}

$form->addValidator('profile_image',
    new Validate_Image(
        $PMDR->getConfig('profile_image_width'),
        $PMDR->getConfig('profile_image_height'),
        $PMDR->getConfig('profile_image_size'),
        $PMDR->getConfig('profile_image_types'),
        true,
        ($PMDR->getConfig('user_add_profile_image') == 'required')
    )
);

if(!$user['terms_accepted'] AND $PMDR->getConfig('reg_terms_checkbox')) {
    $form->addField('terms_text','textarea',array('label'=>$PMDR->getLanguage('user_account_terms'),'fieldset'=>'user_details','readonly'=>'readonly','value'=>$PMDR->getLanguage('user_account_terms_text')));
    $form->addField('terms_accepted','checkbox',array('label'=>$PMDR->getLanguage('user_account_terms'),'fieldset'=>'user_details','html'=>$PMDR->getLanguage('user_account_terms_agree')));
    $form->addValidator('terms_accepted',new Validate_NonEmpty(false));
}

// Text NonEmpty Validators
foreach(array(
    'user_first_name',
    'user_last_name',
    'user_organization',
    'timezone',
    'user_address1',
    'user_address2',
    'user_city',
    'user_state',
    'user_country',
    'user_zip',
    'user_phone',
    'user_fax'
) as $check) {
    if($PMDR->getConfig('user_add_'.$check) == 'required') {
        $form->addValidator($check, new Validate_NonEmpty());
    }
}

$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

$form->loadValues($user);
$form->setFieldAttribute('pass','value','');
if(strstr($user['login'],'RemoteLogin')) {
    $PMDR->addMessage('error', $PMDR->getLanguage('user_account_update_profile'));
    $form->setFieldAttribute('login','value','');
}

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    $data['user_groups'] = $db->GetCol("SELECT group_id FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id=?",array($user['id']));

    if($db->GetRow("SELECT id FROM ".T_USERS." WHERE (user_email=? OR login=?) AND id!=?",array($data['user_email'],$data['user_email'],$user['id']))) {
        $form->errors[] = $PMDR->getLanguage('user_account_email_exists');
    }
    if($db->GetRow("SELECT id FROM ".T_USERS." WHERE login=? AND id!=?",array($data['login'],$user['id']))) {
        $form->errors[] = $PMDR->getLanguage('user_account_username_exists');
    }

    if((!empty($data['pass_new']) OR !empty($data['pass_new_confirm']))) {
        if(!isset($data['pass']) OR !$PMDR->get('Users')->verifyPassword($user['id'],$data['pass'])) {
            $form->errors[] = $PMDR->getLanguage('user_account_password_incorrect');
        }
        if($data['pass_new'] != $data['pass_new_confirm']) {
            $form->errors[] = $PMDR->getLanguage('user_account_new_password_not_matched');
        }
    }
    // After user updates details we need to refresh their session
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        // If we have a new pass (didn't error above) we set it to the normal pass for updating
        if(!empty($data['pass_new'])) {
            $data['pass'] = $data['pass_new'];
        }
        if(!empty($data['timezone'])) {
            $PMDR->get('Session')->set('user_timezone',$data['timezone']);
        }
        // We remove these since we use the auto SQL for user updating
        $form->deleteField('pass_new');
        $form->deleteField('pass_new_confirm');
        $PMDR->get('Users')->update($data, $user['id']);

        $PMDR->get('Email_Templates')->send('admin_user_update',array('user_id'=>$user['id']));

        $PMDR->get('Session')->delete('user_account_update_required');

        $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['login'],$PMDR->getLanguage('user_account_details'))),'update');
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_account.tpl');

if($PMDR->get('Authentication_'.$PMDR->getConfig('login_module'))->remote == true) {
    $template_content->set('remote_login',true);
    $PMDR->get('Authentication_'.$PMDR->getConfig('login_module'))->loadJavascript(BASE_URL.'/modules/login/'.$PMDR->getConfig('login_module').'/'.$PMDR->getConfig('login_module').'_callback_link.php','Link Account');
}

$template_content->set('user',$user);
$template_content->set('form',$form);
$template_content->set('custom_fields',$fields);
include(PMDROOT.'/includes/template_setup.php');
?>