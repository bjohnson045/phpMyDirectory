<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->loadLanguage(array('user_account'));

if(isset($_GET['c'])) {
    $userid = explode('-',$_GET['c']);
    $user = $db->GetRow("SELECT user_email FROM ".T_USERS." WHERE id=?",array($userid[1]));
    if($_GET['c'] == md5($user['user_email'] . LICENSE).'-'.$userid[1]) {
        $db->Execute("DELETE FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id=? AND group_id=5",array($userid[1]));
        if($PMDR->getConfig('user_groups_user_default') == '' OR $PMDR->getConfig('user_groups_user_default') == 0) {
            $user_group = 4;
        } else {
            $user_group = $PMDR->getConfig('user_groups_user_default');
        }
        $db->Execute("INSERT IGNORE ".T_USERS_GROUPS_LOOKUP." (group_id,user_id) VALUES (?,?)",array($user_group,$userid[1]));
        $PMDR->addMessage('success',$PMDR->getLanguage('user_account_email_confirmed'));
        redirect(BASE_URL.MEMBERS_FOLDER,false);
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('user_account_email_confirm_failed'));
        redirect(BASE_URL);
    }
}
?>