<?php
define('PMD_SECTION', 'members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_messages','email_templates'));

$PMDR->get('Authentication')->authenticate();

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_messages_message'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_messages.php','text'=>$PMDR->getLanguage('user_messages')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_messages_posts.php','text'=>$PMDR->getLanguage('user_messages_message')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_messages_posts.tpl');

$form = $PMDR->getNew('Form');
$form->addField('content','textarea',array('label'=>$PMDR->getLanguage('user_messages_message'),'fieldset'=>'input_default'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit')));
$form->addValidator('content',new Validate_NonEmpty());
$template_content->set('form',$form);

$message = $db->GetRow("SELECT m.*,
IF(uto.id = ?,'You',CONCAT(COALESCE(NULLIF(TRIM(CONCAT(uto.user_first_name,' ',uto.user_last_name)),''),uto.login))) AS user_to,
IF(ufrom.id = ?,'You',CONCAT(COALESCE(NULLIF(TRIM(CONCAT(ufrom.user_first_name,' ',ufrom.user_last_name)),''),ufrom.login))) AS user_from
FROM (SELECT * FROM ".T_MESSAGES." WHERE id=? AND (user_id_to=? OR user_id_from=?)) AS m
LEFT JOIN ".T_USERS." uto ON uto.id=m.user_id_to
LEFT JOIN ".T_USERS." ufrom ON ufrom.id=m.user_id_from",array($user['id'],$user['id'],$_GET['message_id'],$user['id'],$user['id']));

if(!$message) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

$db->Execute("UPDATE ".T_MESSAGES." SET date_read=NOW() WHERE id=?",array($message['id']));

$message['date_sent'] = $PMDR->get('Dates_Local')->formatDateTime($message['date_sent']);

$paging = $PMDR->get('Paging');
$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS m.*, CONCAT(COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login)) AS user FROM ".T_MESSAGES_POSTS." m INNER JOIN ".T_USERS." u ON m.user_id=u.id WHERE m.message_id=? ORDER BY date_sent DESC",array($message['id']));
$paging->setTotalResults($db->FoundRows());

foreach($records as $key=>$record) {
    $records[$key]['date_sent'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_sent']);
}

$template_content->set('title',$PMDR->getLanguage('user_messages_message'));
$template_content->set('message_posts',$records);
$template_content->set('message',$message);

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $db->Execute("INSERT INTO ".T_MESSAGES_POSTS." (user_id,message_id,content,date_sent) VALUES (?,?,?,NOW())",array($user['id'],$message['id'],$data['content']));
        if($user['id'] == $message['user_id_from']) {
            $reply_to = $message['user_id_to'];
        } else {
            $reply_to = $message['user_id_from'];
        }
        $PMDR->get('Email_Templates')->send('message_new_reply',array('user_id'=>$reply_to,'variables'=>array('message_id'=>$message['id'])));
        $PMDR->addMessage('success',$PMDR->addMessage('succss',$PMDR->getLanguage('user_messages_reply_added')));
        redirect(array('message_id'=>$message['id']));
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>