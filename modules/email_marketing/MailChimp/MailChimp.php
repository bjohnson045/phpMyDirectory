<?php
/**
* Twilio SMS/Call Class
*/
class MailChimp extends Email_Marketing {
    /**
    * MailChimp API object
    * @var Mailchimp_API
    */
    var $client = null;

    /**
    * Load the Twilio PHP API and initialize the object
    */
    function initialize() {
        include(PMDROOT.'/modules/email_marketing/MailChimp/api/Mailchimp.php');
        $this->connect();
    }

    /**
    * Connect to the API by loading the API authentication details
    */
    function connect() {
        try {
            $this->client = new MailChimp_API($this->settings['mailchimp_api_key']);
        } catch (Mailchimp_Error $e) {
            trigger_error('Error setting Mailchimp API key.',E_USER_WARNING);
            return false;
        }
    }

    /**
    * Get the module name
    * @return string
    */
    function getMarketingName() {
        return 'MailChimp';
    }

    /**
    * Get all email lists from the provider
    * @param boolean $recache
    * @return mixed
    */
    function getLists($recache = false) {
        if(!$recache AND !empty($this->cache['lists'])) {
            return $this->cache['lists'];
        } else {
            $lists_api = new Mailchimp_Lists($this->client);
            $lists = $lists_api->getList();
            $lists_formatted = array();
            if($lists['total'] > 0) {
                foreach($lists['data'] AS $list) {
                    $lists_formatted[$list['id']] = $list['name'];
                }
                $this->cache['lists'] = $lists_formatted;
                $this->saveCache();
                return $lists_formatted;
            } else {
                return false;
            }
        }
    }

    /**
    * Subscribe a list of emails
    * @param int $list_id
    * @param marray $users
    */
    function batchSubscribe($list_id,$users) {
        $users_formatted = array();
        foreach($users AS $user) {
            $users_formatted[] = array('email'=>array('email'=>$user));
        }
        $lists_api = new Mailchimp_Lists($this->client);
        return $lists_api->batchSubscribe($list_id,$users_formatted,false,false,false);
    }

    /**
    * Unsubscribe a list of emails
    * @param int $list_id
    * @param array $users
    */
    function batchUnsubscribe($list_id,$users) {
        $users_formatted = array();
        foreach($users AS $user) {
            $users_formatted[] = array('email'=>array('email'=>$user));
        }
        $lists_api = new Mailchimp_Lists($this->client);
        return $lists_api->batchUnsubscribe($list_id,$users_formatted,false,false,false);
    }
}
?>