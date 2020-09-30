<?php
define('PMD_SECTION','members');

include('../../../defaults.php');

$PMDR->loadLanguage(array('user_account'));

if(LOGGED_IN) {
    $data = array(
        'token'=>$_POST['connection_token'],
        'subdomain'=>$this->PMDR->getConfig('login_module_db_host'),
        'public_key'=>$this->PMDR->getConfig('login_module_db_user'),
        'private_key'=>$this->PMDR->getConfig('login_module_db_password'),
        'format'=>'json'
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://'.$data['subdomain'].'.api.oneall.com/connections/'.$data['token'].'.'.$data['format']);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_USERPWD, $data['public_key'].':'.$data['private_key']);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_VERBOSE, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_FAILONERROR, 0);
    $raw_json = curl_exec($curl);
    curl_close($curl);
    $auth_info = json_decode($raw_json, true);

    if($data['plugin']['key'] != 'social_login' OR $data['plugin']['data']['status'] != 'success') {
        $PMDR->addMessage('error',$PMDR->getLanguage('user_account_link_not'));
    } else {
        if($current_user_id = $db->GetOne("SELECT user_id FROM ".T_USERS_LOGIN_PROVIDERS." WHERE login_id=? AND login_provider=?",array($auth_info['identity']['identity_token'],$auth_info['identity']['provider']))) {
            if($current_user_id == $PMDR->get('Session')->get('user_id')) {
                $db->Execute("UPDATE ".T_USERS_LOGIN_PROVIDERS." SET email=? WHERE user_id=? AND login_id=? AND login_provider=?",array($auth_info['identity']['emails'][0]['value'],$current_user_id,$auth_info['identity']['identity_token'],$auth_info['identity']['provider']));
                $PMDR->addMessage('success',$PMDR->getLanguage('user_account_currently_linked_message'));
            } else {
                $PMDR->addMessage('error',$PMDR->getLanguage('user_account_currently_linked_different'));
            }
        } else {
            $db->Execute("INSERT INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider) VALUES (?,?,?)",array($PMDR->get('Session')->get('user_id'),$auth_info['identity']['identity_token'],$auth_info['identity']['provider']));
            $PMDR->addMessage('success',$PMDR->getLanguage('user_account_linked',$auth_info['identity']['provider']));
        }
    }
}

redirect_url(BASE_URL.MEMBERS_FOLDER.'user_account.php');
?>