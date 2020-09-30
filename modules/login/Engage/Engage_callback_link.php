<?php
define('PMD_SECTION','members');

include('../../../defaults.php');

$PMDR->loadLanguage(array('user_account'));

if(LOGGED_IN) {
    $data = array(
        'token'=>$_POST['token'],
        'apiKey'=>$PMDR->getConfig('login_module_db_password'),
        'format'=>'json'
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $raw_json = curl_exec($curl);
    curl_close($curl);

    $auth_info = json_decode($raw_json, true);

    if($auth_info['stat'] == 'ok') {
        if($current_user_id = $db->GetOne("SELECT user_id FROM ".T_USERS_LOGIN_PROVIDERS." WHERE login_id=? AND login_provider=?",array($auth_info['profile']['identifier'],$auth_info['profile']['providerName']))) {
            if($current_user_id == $PMDR->get('Session')->get('user_id')) {
                $db->Execute("UPDATE ".T_USERS_LOGIN_PROVIDERS." SET email=? WHERE user_id=? AND login_id=? AND login_provider=?",array($auth_info['profile']['email'],$current_user_id,$auth_info['profile']['identifier'],$auth_info['profile']['providerName']));
                $PMDR->addMessage('success',$PMDR->getLanguage('user_account_currently_linked_message'));
            } else {
                $PMDR->addMessage('error',$PMDR->getLanguage('user_account_currently_linked_different'));
            }
        } else {
            $db->Execute("INSERT INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider) VALUES (?,?,?)",array($PMDR->get('Session')->get('user_id'),$auth_info['profile']['identifier'],$auth_info['profile']['providerName']));
            $PMDR->addMessage('success',$PMDR->getLanguage('user_account_linked',$auth_info['profile']['providerName']));
        }
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('user_account_link_not'));
    }
}

redirect_url(BASE_URL.MEMBERS_FOLDER.'user_account.php');
?>