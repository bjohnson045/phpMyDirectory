<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->get('Authentication')->authenticateIP();

if(LOGGED_IN) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_index.php');
}

if($PMDR->getConfig('login_module_registration_url')) {
    redirect_url($PMDR->getConfig('login_module_registration_url'));
}

$PMDR->loadLanguage(array('user_account','email_templates'));

// If user registration is turned off redirect them to the contact page with an error.
if(!$PMDR->getConfig('user_registration')) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_account_add_disabled'));
    redirect_url(BASE_URL.'/contact.php');
}

$users = $PMDR->get('Users');

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_account_registration'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_account_add.php','text'=>$PMDR->getLanguage('user_account_registration')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'members/user_account_add.tpl');

if($PMDR->getConfig('login_module') == 'Engage') {
    $template_content->set('remote_login',true);
    $PMDR->get('Authentication_'.$PMDR->getConfig('login_module'))->loadJavascript();
}

$form = $PMDR->getNew('Form');
$form->enctype = 'multipart/form-data';
$form->addFieldSet('user_details',array('legend'=>$PMDR->getLanguage('user_account_details')));
$form->addFieldSet('address',array('legend'=>$PMDR->getLanguage('user_account_address')));

if($PMDR->getConfig('user_add_login') != 'hidden') {
    $form->addField('login','text',array('label'=>$PMDR->getLanguage('user_account_username'),'fieldset'=>'user_details'));
}
if($PMDR->getConfig('user_add_pass') != 'hidden') {
    $form->addField('pass','password',array('label'=>$PMDR->getLanguage('user_account_password'),'fieldset'=>'user_details','strength'=>true,'strength_label'=>$PMDR->getLanguage('user_account_strength')));
    $form->addField('pass2','password',array('label'=>$PMDR->getLanguage('user_account_password_confirm'),'fieldset'=>'user_details'));
}
$form->addField('user_email','text',array('label'=>$PMDR->getLanguage('user_account_email'),'fieldset'=>'user_details'));
$form->addField('user_email2','text',array('label'=>$PMDR->getLanguage('user_account_email_repeat'),'fieldset'=>'user_details'));
if($PMDR->getConfig('user_display_name')) {
    $form->addField('display_name','text',array('label'=>$PMDR->getLanguage('user_account_display_name'),'fieldset'=>'user_details'));
    $form->addValidator('display_name', new Validate_NonEmpty());
}
if($PMDR->getConfig('user_add_user_first_name') != 'hidden') {
    $form->addField('user_first_name','text',array('label'=>$PMDR->getLanguage('user_account_first_name'),'fieldset'=>'user_details'));
}
if($PMDR->getConfig('user_add_user_last_name') != 'hidden') {
    $form->addField('user_last_name','text',array('label'=>$PMDR->getLanguage('user_account_last_name'),'fieldset'=>'user_details'));
}
if($PMDR->getConfig('user_add_user_organization') != 'hidden') {
    $form->addField('user_organization','text',array('label'=>$PMDR->getLanguage('user_account_organization'),'fieldset'=>'user_details'));
}
if($PMDR->getConfig('user_add_profile_image') != 'hidden') {
    $form->addField('profile_image','file',array('label'=>$PMDR->getLanguage('user_account_profile_picture'),'fieldset'=>'user_details'));
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
}
if($PMDR->getConfig('user_add_timezone') != 'hidden') {
    $form->addField('timezone','select',array('label'=>$PMDR->getLanguage('user_account_timezone'),'fieldset'=>'user_details','first_option'=>'','options'=>include(PMDROOT.'/includes/timezones.php')));
}
if($PMDR->getConfig('user_add_user_address1') != 'hidden') {
    $form->addField('user_address1','text',array('label'=>$PMDR->getLanguage('user_account_address1'),'fieldset'=>'address'));
}
if($PMDR->getConfig('user_add_user_address2') != 'hidden') {
    $form->addField('user_address2','text',array('label'=>$PMDR->getLanguage('user_account_address2'),'fieldset'=>'address'));
}
if($PMDR->getConfig('user_add_user_city') != 'hidden') {
    $form->addField('user_city','text',array('label'=>$PMDR->getLanguage('user_account_city'),'fieldset'=>'address'));
}
if($PMDR->getConfig('user_add_user_state') != 'hidden') {
    $form->addField('user_state','text',array('label'=>$PMDR->getLanguage('user_account_state'),'fieldset'=>'address'));
    $form->addField('user_state_select','select',array('label'=>$PMDR->getLanguage('user_account_state'),'fieldset'=>'address','first_option'=>'','options'=>get_states_array()));
}
if($PMDR->getConfig('user_add_user_country') != 'hidden') {
    $form->addField('user_country','select',array('label'=>$PMDR->getLanguage('user_account_country'),'fieldset'=>'address','first_option'=>'','value'=>$PMDR->getConfig('user_default_country'),'options'=>get_countries_array()));
}
if($PMDR->getConfig('user_add_user_zip') != 'hidden') {
    $form->addField('user_zip','text',array('label'=>$PMDR->getLanguage('user_account_zipcode'),'fieldset'=>'address'));
}
if($PMDR->getConfig('user_add_user_phone') != 'hidden') {
    $form->addField('user_phone','text',array('label'=>$PMDR->getLanguage('user_account_phone'),'fieldset'=>'address'));
}
if($PMDR->getConfig('user_add_user_fax') != 'hidden') {
    $form->addField('user_fax','text',array('label'=>$PMDR->getLanguage('user_account_fax'),'fieldset'=>'address'));
}
$fields = $PMDR->get('Fields')->addToForm($form,'users',array('fieldset'=>'user_details','admin_only'=>false));
if($PMDR->getConfig('GD_security_reg')) {
    $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('user_account_security_code'),'fieldset'=>'user_details'));
    $form->addValidator('security_code',new Validate_Captcha());
}
if($PMDR->getConfig('reg_terms_checkbox')) {
    $form->addField('terms_text','textarea',array('label'=>$PMDR->getLanguage('user_account_terms'),'fieldset'=>'user_details','readonly'=>'readonly','value'=>$PMDR->getLanguage('user_account_terms_text')));
    $form->addField('terms_accepted','checkbox',array('label'=>$PMDR->getLanguage('user_account_terms'),'fieldset'=>'user_details','html'=>$PMDR->getLanguage('user_account_terms_agree')));
    $form->addValidator('terms_accepted',new Validate_NonEmpty(false));
}
$form->addField('ip_address','custom',array('label'=>$PMDR->getLanguage('user_account_logged_ip'),'fieldset'=>'user_details','value'=>get_ip_address(),'html'=>get_ip_address()));
if($email_lists = $db->GetAssoc("SELECT id, title FROM ".T_EMAIL_LISTS." WHERE hidden=0")) {
    $form->addField('email_lists','checkbox',array('label'=>$PMDR->getLanguage('user_account_email_lists'),'fieldset'=>'user_details','value'=>$db->GetCol("SELECT id FROM ".T_EMAIL_LISTS." WHERE hidden=0 AND optout=1"),'options'=>$email_lists));
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

if($PMDR->getConfig('user_add_login') == 'required') {
    $form->addValidator('login', new Validate_Username());
}
if($PMDR->getConfig('user_add_login') == 'enabled') {
    $form->addValidator('login', new Validate_Username(false));
}
$form->addValidator('user_email', new Validate_Email());
$form->addValidator('user_email', new Validate_NonEmpty());
$form->addValidator('user_email2', new Validate_Email());
$form->addValidator('user_email2', new Validate_NonEmpty());
if ($PMDR->getConfig('user_add_pass') == 'required') {
    $form->addValidator('pass', new Validate_Password());
    $form->addValidator('pass2', new Validate_NonEmpty());
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

if($PMDR->getConfig('geolocation_fill') AND empty($values['location']) AND isset($_SESSION['location']) AND $_SESSION['location']) {
    include(PMDROOT.'/includes/country_codes.php');
    $form->loadValues(array(
        'user_city'=>$_SESSION['location']['city'],
        'user_state'=>$_SESSION['location']['region'],
        'user_country'=>$country_codes[$_SESSION['location']['country']]
    ));
}

$PMDR->get('Plugins')->run_hook('user_account_add_form');

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    $data['ip_address'] = get_ip_address();

    $PMDR->get('Plugins')->run_hook('user_account_add_submit');

    if($PMDR->getConfig('user_add_pass') == 'hidden') {
        $data['pass'] = Strings::random(9);
    } elseif($data['pass'] != $data['pass2']) {
        $form->addError($PMDR->getLanguage('user_account_password_not_matched'),'pass');
    }

    if($PMDR->getConfig('user_add_login') == 'hidden') {
        $data['login'] = $data['user_email'];
    } elseif($PMDR->getConfig('user_add_login') == 'enabled' AND $data['login'] == '') {
        $data['login'] = $data['user_email'];
    }

    if($PMDR->getConfig('user_add_pass') != 'hidden') {
        similar_text($data['pass'],$data['login'],$similar_percentage);
        if($similar_percentage > 75) {
            $form->addError($PMDR->getLanguage('user_account_similar_password'),'pass');
        }
    }

    if($data['user_email'] != $data['user_email2']) {
        $form->addError($PMDR->getLanguage('user_account_email_not_matched'),'user_email2');
    }
    if($db->GetRow("SELECT id FROM ".T_USERS." WHERE user_email=?",array($data['user_email'])) AND $data['user_email'] != '') {
        $form->addError($PMDR->getLanguage('user_account_email_exists'),'user_email');
    }
    if($db->GetRow("SELECT id FROM ".T_USERS." WHERE login=?",array($data['login'])) AND $data['login'] != '') {
        $form->addError($PMDR->getLanguage('user_account_username_exists'),'login');
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['user_comment'] = ''; // Some versions of MySQL need this defined (default value)
        if($PMDR->getConfig('email_confirm')) {
            $data['user_groups'] = array(5);
            $PMDR->addMessage('success',$PMDR->getLanguage('user_account_email_confirm'));
            $user_id = $users->insert($data);
            $data['url'] = BASE_URL.MEMBERS_FOLDER.'user_confirm_email.php?c='.md5($data['user_email'].LICENSE).'-'.$user_id;
            if(!empty($_GET['from'])) {
                $data['url'] .= '&from='.urlencode_url($_GET['from']);
                unset($_GET['from']);
            }
        } else {
            $data['user_groups'] = array($PMDR->getConfig('user_groups_user_default'));
            $data['url'] = BASE_URL.MEMBERS_FOLDER;
            $user_id = $users->insert($data);
            // Log in the user
            $_POST['user_login'] = $data['user_email'];
            $_POST['user_pass'] = $data['pass'];
            $PMDR->get('Authentication')->authenticate();
            $PMDR->addMessage('success',$PMDR->getLanguage('user_account_created'));
        }

        $PMDR->get('Plugins')->run_hook('user_account_add_submit_success');

        $PMDR->get('Email_Templates')->send('user_registration',array('to'=>$data['user_email'],'variables'=>array('user_url'=>$data['url'],'user_password'=>$data['pass']),'user_id'=>$user_id));
        $PMDR->get('Email_Templates')->send('admin_user_registration',array('user_id'=>$user_id));

        redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
    }
}
$template_content->set('form',$form);
$template_content->set('custom_fields',$fields);
if(isset($_GET['from'])) {
    $template_content->set('log_in_url',BASE_URL.MEMBERS_FOLDER.'index.php?from='.urlencode_url(URL));
} else {
    $template_content->set('log_in_url',BASE_URL.MEMBERS_FOLDER.'index.php');
}

include(PMDROOT.'/includes/template_setup.php');
?>