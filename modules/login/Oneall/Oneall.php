<?php
class Authentication_Oneall extends AuthenticationUser {
    var $remote = true;

    function loadInput() {
        if(isset($_POST['connection_token'])) {
            return true;
        } else {
            return parent::loadInput();
        }
    }

    function loadJavascript($url = NULL, $title = NULL) {
        if($this->PMDR->getConfig('login_module_db_host') == '') {
            return false;
        }
        if(is_null($url)) {
            $url = BASE_URL.'/modules/login/Oneall/Oneall_callback.php?from='.urlencode_url(URL);
        }
        $this->PMDR->loadJavascript("
<script type=\"text/javascript\">
/* Replace #your_subdomain# by the subdomain of a Site in your OneAll account */
var oneall_subdomain = '".$this->PMDR->getConfig('login_module_db_host')."';

var oa = document.createElement('script');
oa.type = 'text/javascript'; oa.async = true;
oa.src = '//' + oneall_subdomain + '.api.oneall.com/socialize/library.js';
var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(oa, s);

var _oneall = _oneall || [];

_oneall.push(['social_login', 'set_providers', ['facebook','google','linkedin','openid','paypal','twitter','yahoo']]);
_oneall.push(['social_login', 'set_grid_sizes', [2,2]]);
_oneall.push(['social_login', 'set_callback_uri', '".$url."']);
_oneall.push(['social_login', 'do_render_ui', 'social_login_container']);
_oneall.push(['social_login', 'set_popup_usage', 'autodetect']);
_oneall.push(['social_login', 'set_custom_css_uri', '//oneallcdn.com/css/api/themes/beveled_connect_w208_h30_wc_v1.css']);
</script>"
        ,15);
    }

    function verifyLogin() {
        if(!isset($_POST['connection_token'])) {
            return parent::verifyLogin();
        }

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
        $json = json_decode($raw_json, true);

        $data = $json['response']['result']['data'];
        $user = $json['response']['result']['data']['user'];

        if($data['plugin']['key'] != 'social_login' OR $data['plugin']['data']['status'] != 'success') {
            return false;
        }

        $user_data = array(
            'cookie_salt'=>$this->generateSalt(),
            'password_hash'=>$this->encryption,
            'user_groups'=>array(4)
        );

        if(isset($user['identity']['emails'][0]['value']) AND !is_null($user['identity']['emails'][0]['value'])) {
            $user_data['user_email'] = $user['identity']['emails'][0]['value'];
        } else {
            $user_data['user_email'] = 'oneallEmailPlaceHolder';
        }

        if(isset($user['identity']['pictureUrl']) AND $user['identity']['pictureUrl'] != '') {
            $user_data['profile_image'] = $user['identity']['pictureUrl'];
        }

        if(isset($user['identity']['name']['formatted'])) {
            $formatted_name = explode(' ',$user['identity']['name']['formatted']);
            if(count($formatted_name) == 2) {
                $user_data['user_first_name'] = $formatted_name[0];
                $user_data['user_last_name'] = $formatted_name[1];
            } else {
                $user_data['user_first_name'] = $formatted_name[0];
            }
            unset($formatted_name);
        }

        if(!($users = $this->db->GetAll("SELECT u.id, u.password_salt, u.cookie_salt, u.login, u.user_email FROM ".T_USERS." u INNER JOIN ".T_USERS_LOGIN_PROVIDERS." ulp ON u.id=ulp.user_id WHERE ulp.login_id=?",array($user['identity']['identity_token'])))) {
            if($this->db->GetRow("SELECT id FROM ".T_USERS." WHERE login=?",array($user_data['login']))) {
                $user_data['login'] = '';
            }

            $user_data['password_salt'] = $this->generateSalt();
            $user_data['pass'] = $this->encryptPassword(Strings::random(8),$user_data['password_salt'],$this->encryption);

            $this->user['id'] = $this->PMDR->get('Users')->insert($user_data);
            $this->db->Execute("INSERT INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider,email) VALUES (?,?,?,?)",array($this->user['id'],$user['identity']['identity_token'],$user['identity']['provider'],$user_data['user_email']));
            if(empty($user_data['login'])) {
                $user_data['login'] = 'RemoteLogin_'.$this->user['id'];
                $this->db->Execute("UPDATE ".T_USERS." SET login='".$user_data['login']."' WHERE id=?",array($this->user['id']));
            }

            $this->PMDR->loadLanguage(array('email_templates'));
            if(!empty($user_data['user_email'])) {
                $this->PMDR->get('Email_Templates')->send('user_registration',array('to'=>$user_data['user_email'],'variables'=>array('user_password'=>'********'),'user_id'=>$this->user['id']));
            }
            $this->PMDR->get('Email_Templates')->send('admin_user_registration',array('user_id'=>$this->user['id']));

            $this->loadUser($user_data['login']);
            return true;
        } else {
            if(count($users) > 1) {
                return false;
            }
            unset($user_data['user_email']);
            $this->PMDR->get('Users')->update($user_data,$users[0]['id']);
            $this->db->Execute("UPDATE ".T_USERS_LOGIN_PROVIDERS." SET email=? WHERE user_id=? AND login_id=? AND login_provider=?",array($user_data['user_email'],$users[0]['id'],$user['identity']['identity_token'],$user['identity']['provider']));
            $this->loadUser($users[0]['login']);
            return true;
        }
    }
}
?>