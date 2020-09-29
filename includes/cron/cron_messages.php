<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_messages($j) {
    global $PMDR, $db;

    $messages = $db->GetAll("SELECT id, user_id_to FROM ".T_MESSAGES." WHERE notification_sent=0 LIMIT 50");
    foreach($messages AS $message) {
        $PMDR->get('Email_Templates')->send('message_new',array('user_id'=>$message['user_id_to']));
        $db->Execute("UPDATE ".T_MESSAGES." SET notification_sent=1 WHERE id=?",array($message['id']));
    }
    return array('status'=>true);
}

// Add the CRON job to the queue and set it to run based on the backup CRON days setting
$cron['cron_messages'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>25);
?>