<?php
define('PMD_SECTION', 'members');

include('../defaults.php');

$PMDR->loadLanguage(array('user_messages'));

$PMDR->get('Authentication')->authenticate();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_messages'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_messages.php','text'=>$PMDR->getLanguage('user_messages')));

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_MESSAGES." WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_messages'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_messages.tpl');

$table_list = $PMDR->get('TableList');
$table_list->addColumn('title');
$table_list->addColumn('user_from',$PMDR->getLanguage('user_messages_from'));
$table_list->addColumn('user_to',$PMDR->getLanguage('user_messages_to'));
$table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
$paging = $PMDR->get('Paging');
$records = $db->GetAll("SELECT m.*,
IF(uto.id = ?,'You',CONCAT(COALESCE(NULLIF(TRIM(CONCAT(uto.user_first_name,' ',uto.user_last_name)),''),uto.login))) AS user_to,
IF(ufrom.id = ?,'You',CONCAT(COALESCE(NULLIF(TRIM(CONCAT(ufrom.user_first_name,' ',ufrom.user_last_name)),''),ufrom.login))) AS user_from
FROM (SELECT * FROM ".T_MESSAGES." WHERE user_id_to=? OR user_id_from=? ORDER BY date_sent DESC LIMIT ?,?) AS m
LEFT JOIN ".T_USERS." uto ON uto.id=m.user_id_to
LEFT JOIN ".T_USERS." ufrom ON ufrom.id=m.user_id_from ORDER BY date_sent DESC",array($user['id'],$user['id'],$user['id'],$user['id'],$paging->limit1,$paging->limit2));
$table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_MESSAGES." WHERE user_id_to=?",array($user['id'])));
$table_list->addRecords($records);
$table_list->addPaging($paging);
$template_content->set('title',$PMDR->getLanguage('user_messages'));
$table_list->addToTemplate($template_content);

include(PMDROOT.'/includes/template_setup.php');
?>