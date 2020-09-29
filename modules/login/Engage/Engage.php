<?php
class Authentication_Engage extends AuthenticationUser {
    var $remote = true;

    function loadInput() {
        if(isset($_POST['token'])) {
            return true;
        } else {
            return parent::loadInput();
        }
    }

    function loadJavascript($url = NULL, $title = NULL) {
        if(is_null($url)) {
            $url = BASE_URL.'/modules/login/Engage/Engage_callback.php?from='.urlencode_url(URL);
        }
        if(is_null($title)) {
            $title = $this->PMDR->getLanguage('user_index_login');
        }
        $this->PMDR->loadJavascript('
        <script type="text/javascript">
        (function() {
            if (typeof window.janrain !== \'object\') window.janrain = {};
            if (typeof window.janrain.settings !== \'object\') window.janrain.settings = {};
            janrain.settings.tokenUrl = "'.$url.'";
            function isReady() { janrain.ready = true; };
            if (document.addEventListener) {
              document.addEventListener("DOMContentLoaded", isReady, false);
            } else {
              window.attachEvent(\'onload\', isReady);
            }
            var e = document.createElement(\'script\');
            e.type = \'text/javascript\';
            e.id = \'janrainAuthWidget\';
            if (document.location.protocol === "https:") {
              e.src = "https://rpxnow.com/js/lib/'.$this->PMDR->getConfig('login_module_db_user').'/engage.js";
            } else {
              e.src = "http://widget-cdn.rpxnow.com/js/lib/'.$this->PMDR->getConfig('login_module_db_user').'/engage.js";
            }
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(e, s);
        })();
        </script>
        ',15);
        $this->PMDR->loadJavascript('
        <script type="text/javascript">
            $(document).ready(function(){
                $("#remote_login_link").click(function(e) {
                    e.preventDefault();
                    $(\'<div><iframe style="width: 100%; height: 100%" marginwidth="0" marginheight="0" frameborder="0" scrolling="auto" id="#rpx" src="'.URL_SCHEME.'://'.$this->PMDR->getConfig('login_module_db_user').'.rpxnow.com/openid/embed?token_url='.$url.'" /></div>\').dialog({
                        title: "'.$title.'",
                        autoOpen: true,
                        width: 400,
                        height: 300,
                        modal: true,
                        resizable: false
                    });
                });
            });
        </script>',15);
    }

    function verifyLogin() {
        if(!isset($_POST['token'])) {
            return parent::verifyLogin();
        }

        $data = array(
            'token'=>$_POST['token'],
            'apiKey'=>$this->PMDR->getConfig('login_module_db_password'),
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

        if($auth_info['stat'] != 'ok') {
            // $auth_info['err']['msg']
            return false;
        }

        $user_data = array(
            'cookie_salt'=>$this->generateSalt(),
            'password_hash'=>$this->encryption,
            'user_groups'=>array(4)
        );

        if(isset($auth_info['profile']['preferredUsername']) AND !empty($auth_info['profile']['preferredUsername'])) {
            $user_data['login'] = $auth_info['profile']['preferredUsername'];
        }

        if(isset($auth_info['profile']['email']) AND !is_null($auth_info['profile']['email'])) {
            $user_data['user_email'] = $auth_info['profile']['email'];
        } else {
            $user_data['user_email'] = '';
        }
        if(isset($auth_info['profile']['name']['familyName'])) {
            $user_data['user_last_name'] = $auth_info['profile']['name']['familyName'];
            if(isset($auth_info['profile']['name']['givenName'])) {
                $user_data['user_first_name'] = $auth_info['profile']['name']['givenName'];
            }
        } elseif(isset($auth_info['profile']['name']['formatted'])) {
            $formatted_name = explode(' ',$auth_info['profile']['name']['formatted']);
            if(count($formatted_name) == 2) {
                $user_data['user_first_name'] = $formatted_name[0];
                $user_data['user_last_name'] = $formatted_name[1];
            } else {
                $user_data['user_first_name'] = $formatted_name[0];
            }
            unset($formatted_name);
        }
        if(isset($auth_info['profile']['photo'])) {
            $user_data['profile_image'] = $auth_info['profile']['photo'];
        }
        if(isset($auth_info['profile']['phoneNumber'])) {
            $user_data['user_phone'] = $auth_info['profile']['phoneNumber'];
        }
        if(isset($auth_info['profile']['address']['streetAddress'])) {
            $user_data['user_address1'] = $auth_info['profile']['address']['streetAddress'];
        }
        if(isset($auth_info['profile']['address']['locality'])) {
            $user_data['user_city'] = $auth_info['profile']['address']['locality'];
        }
        if(isset($auth_info['profile']['address']['region'])) {
            $user_data['user_state'] = $auth_info['profile']['address']['region'];
        }
        if(isset($auth_info['profile']['address']['country'])) {
            $user_data['user_country'] = $auth_info['profile']['address']['country'];
        }
        if(isset($auth_info['profile']['address']['postalCode'])) {
            $user_data['user_zip'] = $auth_info['profile']['address']['postalCode'];
        }

        if(!($users = $this->db->GetAll("SELECT u.id, u.password_salt, u.cookie_salt, u.login, u.user_email FROM ".T_USERS." u INNER JOIN ".T_USERS_LOGIN_PROVIDERS." ulp ON u.id=ulp.user_id WHERE ulp.login_id=?",array($auth_info['profile']['identifier'])))) {
            if($this->db->GetRow("SELECT id FROM ".T_USERS." WHERE login=?",array($user_data['login']))) {
                $user_data['login'] = '';
            }

            $user_data['password_salt'] = $this->generateSalt();
            $user_data['pass'] = $this->encryptPassword(Strings::random(8),$user_data['password_salt'],$this->encryption);

            $this->user['id'] = $this->PMDR->get('Users')->insert($user_data);
            $this->db->Execute("INSERT INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider,email) VALUES (?,?,?,?)",array($this->user['id'],$auth_info['profile']['identifier'],$auth_info['profile']['providerName'],$auth_info['profile']['email']));
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
            $this->db->Execute("UPDATE ".T_USERS_LOGIN_PROVIDERS." SET email=? WHERE user_id=? AND login_id=? AND login_provider=?",array($user_data['user_email'],$users[0]['id'],$auth_info['profile']['identifier'],$auth_info['profile']['providerName']));
            unset($user_data['user_email']);
            $this->PMDR->get('Users')->update($user_data,$users[0]['id']);
            $this->loadUser($users[0]['login']);
            return true;
        }
    }
}
?>