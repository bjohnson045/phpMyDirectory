<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_messages','admin_users','admin_users_merge','admin_contact_requests'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_MESSAGES." WHERE id=?",array($_GET['id']));
    $db->Execute("DELETE FROM ".T_MESSAGES_POSTS." WHERE message_id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_messages'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id');
    $table_list->addColumn('from');
    $table_list->addColumn('to');
    $table_list->addColumn('title');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT m.*,
    uto.user_first_name AS user_first_name_to, uto.user_last_name AS user_last_name_to, uto.login AS login_to,
    ufrom.user_first_name AS user_first_name_from, ufrom.user_last_name AS user_last_name_from, ufrom.login AS login_from
    FROM (SELECT * FROM ".T_MESSAGES." ORDER BY date_sent DESC LIMIT ?,?) AS m
    LEFT JOIN ".T_USERS." uto ON uto.id=m.user_id_to
    LEFT JOIN ".T_USERS." ufrom ON ufrom.id=m.user_id_from ORDER BY date_sent DESC",array($paging->limit1,$paging->limit2));

    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_MESSAGES));

    foreach($records as $key=>$record) {
        $records[$key]['to'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id_to'].'">';
        $records[$key]['to'] .= trim($record['user_first_name_to'].' '.$record['user_last_name_to']) != '' ? trim($record['user_first_name_to'].' '.$record['user_last_name_to']) : $record['login_to'];
        $records[$key]['to'] .= '</a> (ID: '.$record['user_id_to'].')';
        $records[$key]['from'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id_from'].'">';
        $records[$key]['from'] .= trim($record['user_first_name_from'].' '.$record['user_last_name_from']) != '' ? trim($record['user_first_name_from'].' '.$record['user_last_name_from']) : $record['login_from'];
        $records[$key]['from'] .= '</a> (ID: '.$record['user_id_from'].')';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('comments',array('href'=>'admin_messages_posts.php?message_id='.$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_messages'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('message',array('legend'=>$PMDR->getLanguage('admin_messages_message')));
    $form->addField('from_user','custom',array('label'=>$PMDR->getLanguage('admin_messages_from')));
    $form->addField('to_user','custom',array('label'=>$PMDR->getLanguage('admin_messages_to')));
    $form->addField('date_sent','custom');
    $form->addField('title','text');
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_messages_edit'));
        $message = $db->GetRow("SELECT *,
        CONCAT(COALESCE(NULLIF(TRIM(CONCAT(uto.user_first_name,' ',uto.user_last_name)),''),uto.login)) AS to_user,
        CONCAT(COALESCE(NULLIF(TRIM(CONCAT(ufrom.user_first_name,' ',ufrom.user_last_name)),''),ufrom.login)) AS from_user
        FROM ".T_MESSAGES." m
        LEFT JOIN ".T_USERS." uto ON uto.id=m.user_id_to
        LEFT JOIN ".T_USERS." ufrom ON ufrom.id=m.user_id_from
        WHERE m.id=?",array($_GET['id']));
        $message['date_sent'] = $PMDR->get('Dates_Local')->formatDate($message['date_sent']);
        $form->loadValues($message);
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action'] == 'edit') {
                $db->Execute("UPDATE ".T_MESSAGES." SET title=? WHERE id=?",array($data['title'],$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_messages_message'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>