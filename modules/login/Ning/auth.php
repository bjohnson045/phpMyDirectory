<?php
/**
 *    Ning Authentification API and plugin configuration options.
 */
class NingIdApi {
    /**
     *  Checks whether domain is valid.
     *
     *  @param      $domain        string        Domain name (w/o schema and path)
     *  @return     boolean
     */
    function checkDomain($domain) {
        $url = "http://$domain/main/external/info?format=serialize";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        if ($result && is_array($info = unserialize($result)) && $info['version']) {
            return true;
        }
        return false;
    }

    /**
     *  Authorizes ning user and returns some information about him/her.
     *  Returns array with user information or NULL if authentification fails
     *
     *  @param      $domain        string        Ning network domain (w/o schema and path)
     *  @param        $email        string        Screen name or email
     *  @param        $password    string        Password
     *  @return     {name, email, avatar_url}
     */
    function authorize($domain, $email, $password) {
        $url = "http://$domain/main/external/auth?format=serialize";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "email=".urlencode($email)."&password=".urlencode($password));
        curl_setopt($curl, CURLOPT_USERAGENT, "botd Mozilla/4.0 (Compatible; Ning Auth API)");
        $result = curl_exec($curl);
        if ($result && is_array($info = unserialize($result)) && count($info) && $info['email'] && $info['name']) {
            return $info;
        }
        return NULL;
    }
}
?>