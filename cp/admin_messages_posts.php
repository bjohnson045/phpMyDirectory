<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_messages','admin_users','admin_users_merge','admin_contact_requests'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_MESSAGES_POSTS." WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_messages_posts'))),'delete');
    redirect(array('message_id'=>$_GET['message_id']));
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_messages_posts.tpl');

if(!isset($_GET['action'])) {
    $message = $db->GetRow("SELECT m.*,
    CONCAT(COALESCE(NULLIF(TRIM(CONCAT(uto.user_first_name,' ',uto.user_last_name)),''),uto.login)) AS user_to,
    CONCAT(COALESCE(NULLIF(TRIM(CONCAT(ufrom.user_first_name,' ',ufrom.user_last_name)),''),ufrom.login)) AS user_from
    FROM (SELECT * FROM ".T_MESSAGES." WHERE id=?) AS m
    LEFT JOIN ".T_USERS." uto ON uto.id=m.user_id_to
    LEFT JOIN ".T_USERS." ufrom ON ufrom.id=m.user_id_from",array($_GET['message_id']));

    $message['date_sent'] = $PMDR->get('Dates_Local')->formatDateTime($message['date_sent']);

    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS m.*, CONCAT(COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login)) AS user FROM ".T_MESSAGES_POSTS." m INNER JOIN ".T_USERS." u ON m.user_id=u.id WHERE m.message_id=? ORDER BY date_sent DESC",array($_GET['message_id']));
    $paging->setTotalResults($db->FoundRows());

    foreach($records as $key=>$record) {
        $records[$key]['date_sent'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_sent']);
    }

    $template_content->set('title',$PMDR->getLanguage('admin_messages_posts'));
    $template_content->set('message_posts',$records);
    $template_content->set('message',$message);
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>