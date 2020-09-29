<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_reminders($j) {
    global $PMDR, $db;

    // Get all unconfirmed users less than 30 days old and that have been registered for one of 7, 14, 21, or 28 days
    // In other words, send a reminder email 4 times every 7 days after a user registers if they remain unconfirmed
    $unconfirmed_users = $db->GetAll("SELECT u.id, u.user_email FROM ".T_USERS." u INNER JOIN ".T_USERS_GROUPS_LOOKUP." ugl ON u.id=ugl.user_id WHERE ugl.group_id=5 AND DATEDIFF(NOW(),u.created) BETWEEN 1 AND 30 AND MOD(DATEDIFF(NOW(),u.created),7) = 0");
    foreach($unconfirmed_users AS $user) {
        $variables['user_url'] = BASE_URL.MEMBERS_FOLDER.'user_confirm_email.php?c='.md5($user['user_email'].LICENSE).'-'.$user['id'];
        $PMDR->get('Email_Templates')->queue('user_email_confirmation_reminder',array('to'=>$user['id'],'user_id'=>$user['id'],'variables'=>$variables));
    }

    return array('status'=>true);
}
$cron['cron_reminders'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>6);
?>