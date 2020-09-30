<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_users','admin_users_merge','admin_contact_requests','admin_messages'));

$PMDR->get('Authentication')->checkPermission('admin_users_edit');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $form = $PMDR->getNew('Form');
    $form->addFieldSet('user_details',array('legend'=>$PMDR->getLanguage('admin_users_merge_information')));
    if($PMDR->getConfig('user_select') == 'select_window') {
        $form->addField('user_from','select_window',array('label'=>$PMDR->getLanguage('admin_users_merge_from'),'fieldset'=>'user_details','icon'=>'users_search','options'=>'select_user'));
        $form->addField('user_to','select_window',array('label'=>$PMDR->getLanguage('admin_users_merge_to'),'fieldset'=>'user_details','icon'=>'users_search','options'=>'select_user'));
    } else {
        $form->addField('user_from','select',array('label'=>$PMDR->getLanguage('admin_users_merge_from'),'fieldset'=>'user_details','first_option'=>'','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
        $form->addField('user_to','select',array('label'=>$PMDR->getLanguage('admin_users_merge_to'),'fieldset'=>'user_details','first_option'=>'','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
    }
    if(isset($_GET['from_id'])) {
        $form->loadValues(array('user_from'=>$_GET['from_id']));
    }
    $form->addField('remove','checkbox',array('label'=>$PMDR->getLanguage('admin_users_merge_remove'),'fieldset'=>'user_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('user_from',new Validate_NonEmpty());
    $form->addValidator('user_to',new Validate_NonEmpty());

    $template_content->set('title',$PMDR->getLanguage('admin_users_merge'));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Users')->merge($data['user_from'],$data['user_to'],$data['remove']);
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_users_merge_merged',array($data['user_from'],$data['user_to'])));
            redirect_url(BASE_URL_ADMIN.'/admin_users_summary.php?id='.$data['user_to']);
        }
    }
    $template_content->set('content', $form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>