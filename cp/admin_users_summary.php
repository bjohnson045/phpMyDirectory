<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_users','admin_users_merge','email_templates','admin_email_log'));

$PMDR->get('Authentication')->checkPermission('admin_users_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_users_summary.tpl');

if(!isset($_GET['id']) OR empty($_GET['id']) OR !$user = $PMDR->get('User',$_GET['id'])) {
    redirect_url(BASE_URL_ADMIN.'/admin_users.php');
}

$template_content->set('users_summary_header',$user->getAdminSummaryHeader('summary'));

if(isset($_GET['action']) AND $_GET['action'] == 'reset_password') {
    $new_pass = $PMDR->get('Users')->resetPassword($user->id);
    $PMDR->get('Email_Templates')->send('password_reset',array('to'=>$user->user_email,'variables'=>array('user_new_password'=>$new_pass),'user_id'=>$user->id));
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_users_password_reset_email_sent'));
    redirect(null,array('id'=>$_GET['id']));
}

foreach($user->data AS $key=>$value) {
    $template_content->set($key,$value);
}

$fields = $PMDR->get('Fields')->getFields('users');
$template_content->set('fields',$fields);

$template_content->set('created',$PMDR->get('Dates_Local')->formatDateTime($user->created));
$template_content->set('logged_last',$PMDR->get('Dates_Local')->formatDateTime($user->logged_last));
$template_content->set('profile_image',$user->getProfileImageURL());
$template_content->set('disable_overdue_notices',$PMDR->get('HTML')->icon($user->disable_overdue_notices));
$template_content->set('tax_exempt',$PMDR->get('HTML')->icon($user->tax_exempt));
$template_content->set('comments',nl2br($user->user_comment));
$template_content->set('logged_in',$PMDR->get('HTML')->icon($user->loggedIn() ? '1' : '0'));
if(!empty($user->timezone)) {
    $template_content->set('local_time',$PMDR->get('Dates')->formatDateTime(date('Y-m-d H:i:s',time()+$PMDR->get('Dates')->getOffset($user->timezone))));
}
$recent_emails = $db->GetAll("SELECT * FROM ".T_EMAIL_LOG." WHERE user_id=? ORDER BY date DESC LIMIT 5",$user->id);
foreach($recent_emails AS &$recent_email) {
    $recent_email['date'] = $PMDR->get('Dates_Local')->formatDateTime($recent_email['date']);
}
$template_content->set('recent_emails',$recent_emails);

$email_lists = $db->GetAll("SELECT title FROM ".T_EMAIL_LISTS_LOOKUP." ll INNER JOIN ".T_EMAIL_LISTS." l ON ll.list_id=l.id WHERE ll.user_id=?",array($user->id));
$template_content->set('email_lists',$email_lists);

$user_groups = $db->GetAssoc("SELECT id, name FROM ".T_USERS_GROUPS." ug INNER JOIN ".T_USERS_GROUPS_LOOKUP." ugl ON ug.id=ugl.group_id WHERE ugl.user_id=?",array($user->id));
$template_content->set('user_groups',$user_groups);

if(isset($user_groups[1]) AND defined('SECURITY_KEY')) {
    $template_content->set('api_username',hash('sha256',$user['login'].SECURITY_KEY));
    $template_content->set('api_password','');
}

$email_form = $PMDR->get('Form');
// We do not include the password reset template as we have a link used for that
$email_options = $db->GetAssoc("SELECT id, id FROM ".T_EMAIL_TEMPLATES." WHERE type='user' AND id NOT LIKE 'admin_%' AND id NOT IN('password_reset','message_new','message_new_reply')");
foreach($email_options AS $id) {
    $email_options[$id] = $PMDR->getLanguage('email_templates_'.$id.'_name');
}
$email_options = array_merge(array('new_email'=>$PMDR->getLanguage('admin_users_new_email')),$email_options);
$email_form->addField('email','select',array('label'=>'','options'=>$email_options));
$email_form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$template_content->set('login_providers',implode(', ',$db->GetCol("SELECT login_provider FROM ".T_USERS_LOGIN_PROVIDERS." WHERE user_id=?",array($user->id))));

if($map = $PMDR->get('Map')) {
    $map->mapID = 'user_summary_map';
    $map->addMarkerByAddress($user->user_address1,$user->user_city,$user->user_state,$user->user_country,$user->user_zip);
    if(count($map->markers)) {
        $PMDR->loadJavascript($map->getHeaderJS());
        $PMDR->loadJavascript($map->getMapJS());
        $PMDR->setAdd('javascript_onload','mapOnLoad();');
        $map_output = $map->getMap();
        $template_content->set('map',$map_output);
    }
}

if($email_form->wasSubmitted('submit')) {
    $data = $email_form->loadValues();
    if(!$email_form->validate()) {
        $PMDR->addMessage('error',$email_form->parseErrorsForTemplate());
    } else {
        if($data['email'] == 'new_email') {
            redirect('admin_email_send.php',array('template'=>'new','user_id'=>$_GET['id']));
        } else {
            $variables = array();
            if($data['email'] == 'password_reset_request') {
                $variables['user_password_reminder_url'] = BASE_URL.MEMBERS_FOLDER.'user_password_remind.php?id='.$user->id.'&verify='.md5(SECURITY_KEY.$user->user_email);
            }
            if($data['email'] == 'user_email_confirmation_reminder') {
                $variables['user_url'] = BASE_URL.MEMBERS_FOLDER.'user_confirm_email.php?c='.md5($user->user_email.SECURITY_KEY).'-'.$user->id;
            }
            $PMDR->get('Email_Templates')->send($data['email'],array('to'=>$user->user_email,'user_id'=>$user->id,'variables'=>$variables));
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_users_email_sent'));
            redirect(null,array('id'=>$_GET['id']));
        }
    }
}

$template_content->set('email_form',$email_form);

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>