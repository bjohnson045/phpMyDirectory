<?php
if(!defined('IN_PMD')) exit();

$PMDR->get('Plugins')->run_hook('template_setup_begin');

if(LOGGED_IN AND isset($_SESSION['user_account_update_required']) AND !isset($_SESSION['admin_id']) AND !on_page(MEMBERS_FOLDER.'user_account.php') AND (PMD_SECTION == 'public' OR PMD_SECTION == 'members')) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_account.php?update_required=true');
}

include(PMDROOT.'/includes/common_header.php');

if($PMDR->get('wrapper_file') AND $PMDR->get('Templates')->path($PMDR->get('wrapper_file'))) {
    $template_wrapper = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.$PMDR->get('wrapper_file'));
} elseif(is_null($PMDR->get('wrapper_file')) AND is_object($template_content)) {
    $template_wrapper = $template_content;
} elseif(PMD_SECTION == 'members') {
    $template_wrapper = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_wrapper.tpl');
    $template_wrapper->set('contact_requests',intval($PMDR->getConfig('contact_requests_limit')));
    if(LOGGED_IN) {
        $template_wrapper->set('messages_count',$db->GetOne("SELECT COUNT(*) FROM ".T_MESSAGES." m LEFT JOIN ".T_MESSAGES_POSTS." mp ON m.id=mp.message_id WHERE (m.user_id_to=? OR m.user_id_from=?) AND (m.date_read < mp.date_sent OR m.date_read IS NULL) GROUP BY m.id",array($PMDR->get('Session')->get('user_id'),$PMDR->get('Session')->get('user_id'))));
    }
} else {
    $template_wrapper = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'wrapper.tpl');
}

$template_wrapper->set('banners',$PMDR->get('Banner_Display'));

$PMDR->get('Plugins')->run_hook('template_setup_wrapper');

if($PMDR->get('page_header') !== null) {
    if($PMDR->get('page_header')) {
        $page_header = $PMDR->get('page_header');
    } elseif(PMD_SECTION == 'members') {
        $page_header = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_page_header.tpl');
    } else {
        $page_header = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_header.tpl');
    }
    $page_title = (array) $PMDR->get('page_title');
    $page_header->set('title',array_pop($page_title));
    unset($page_title);
    $template_wrapper->set('page_header',$page_header);
}

if(is_array($PMDR->get('breadcrumb')) AND count($PMDR->get('breadcrumb'))) {
    $template_wrapper->set('breadcrumb',$PMDR->get('breadcrumb'));
} else {
    $template_wrapper->set('breadcrumb',false);
}

$template_message = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/message.tpl');
$template_message->set('message_types',$PMDR->getMessages());
$template_wrapper->set('message',$template_message);
$template_wrapper->set('template_content',$template_content);

include(PMDROOT.'/includes/template_header.php');
include(PMDROOT.'/includes/template_footer.php');

$template_wrapper->set('header',$header);
$template_wrapper->set('footer',$footer);

$PMDR->get('Plugins')->run_hook('template_setup');

echo $template_wrapper->render();

$PMDR->get('Plugins')->run_hook('template_setup_end');

include(PMDROOT.'/includes/common_footer.php');
?>