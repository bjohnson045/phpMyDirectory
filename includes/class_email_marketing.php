<?php
/**
* Class SMS
* Base class for SMS handling
*/
abstract class Email_Marketing {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * Settings for the specific email marketer
    * @var mixed
    */
    var $settings;
    /**
    * Cache for API call data
    * @var array
    */
    var $cache = array();

    /**
    * SMS Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->loadSettings();
        $this->initialize();
    }

    abstract protected function initialize();
    abstract public function getLists($recache = false);
    abstract public function getMarketingName();
    abstract public function batchSubscribe($list_id,$users);
    abstract public function batchUnsubscribe($list_id,$users);

    /**
    * Load SMS settings from database
    * @return void
    */
    protected function loadSettings() {
        $this->settings = $this->db->GetRow("SELECT id, settings, cache FROM ".T_EMAIL_MARKETING." WHERE id=?",array(get_class($this)));
        if(!empty($this->settings['cache'])) {
            $this->cache = unserialize($this->settings['cache']);
        }
        unset($this->settings['cache']);
        if(!empty($this->settings['settings'])) {
            if($settings = unserialize($this->settings['settings'])) {
                $this->settings = array_merge($this->settings,$settings);
            } else {
                trigger_error('Unable to unserialize email marketing settings.',E_USER_WARNING);
            }
        }
    }

    /**
    * Subscribe an email address to a list
    * @param int $list_id
    * @param string $email
    */
    protected function subscribe($list_id,$email) {
        if($user = $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE user_email=?",array($email))) {
            if($list_id = $this->db->GetOne("SELECT id FROM ".T_EMAIL_LISTS." WHERE email_marketing_list_id=?",array($list_id))) {
                $this->db->Execute("REPLACE INTO ".T_EMAIL_LISTS_LOOKUP." (list_id,user_id) VALUES (?,?)",array($list_id,$user['id']));
            }
        }
    }

    /**
    * Unsubscribe an email address to a list
    * @param int $list_id
    * @param string $email
    */
    protected function unsubscribe($list_id,$email) {
        if($user = $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE user_email=?",array($email))) {
            if($list_id = $this->db->GetOne("SELECT id FROM ".T_EMAIL_LISTS." WHERE email_marketing_list_id=?",array($list_id))) {
                $this->db->Execute("DELETE FROM ".T_EMAIL_LISTS_LOOKUP." WHERE list_id=? AND user_id=?",array($list_id,$user['id']));
            }
        }
    }

    /**
    * Save the email marketing cache
    */
    protected function saveCache() {
        $this->db->Execute("UPDATE ".T_EMAIL_MARKETING." SET cache=? WHERE id=?",array(serialize($this->cache),$this->settings['id']));
    }

    /**
    * Queue all users to a specific list and action
    * @param int $list_id
    * @param string $action
    */
    public function queueAll($list_id, $action = 'subscribe') {
        $this->db->Execute("INSERT INTO ".T_EMAIL_MARKETING_QUEUE." (user_id,list_id,action,date_queued) SELECT u.id, ?, ?, NOW() FROM ".T_USERS." u WHERE u.id NOT IN (SELECT user_id FROM ".T_EMAIL_LISTS_LOOKUP." WHERE list_id=?)",array($list_id,$action,$list_id));
    }

    /**
    * Unlink a list and remove any actions from the queue
    * @param int $list_id
    */
    public function unlinkList($list_id) {
        $this->db->Execute("DELETE FROM ".T_EMAIL_MARKETING_QUEUE." WHERE list_id=?",array($list_id));
    }

    /**
    * Unlink all lists and remove all actions from the queue
    */
    public function unlinkLists() {
        $this->db->Execute("UPDATE ".T_EMAIL_LISTS." SET email_marketing_list_id=''");
        $this->db->Execute("DELETE FROM ".T_EMAIL_MARKETING_QUEUE);
    }

    /**
    * Sync a list by subscribing all users from the list
    * @param int $list_id
    */
    public function syncList($list_id) {
        $this->db->Execute("INSERT INTO ".T_EMAIL_MARKETING_QUEUE." (user_id,list_id,action,date_queued) SELECT user_id, list_id, 'subscribe', NOW() FROM ".T_EMAIL_LISTS_LOOKUP." WHERE list_id=?",array($list_id));
    }

    /**
    * Process the email marketing queue
    * @param int $amount
    */
    public function processQueue($amount = 200) {
        $this->db->Execute("DELETE mq.* FROM ".T_EMAIL_MARKETING_QUEUE." mq, ".T_EMAIL_LISTS." l WHERE mq.list_id=l.id AND l.email_marketing_list_id=''");

        $lists = $this->db->GetAssoc("SELECT id, email_marketing_list_id FROM ".T_EMAIL_LISTS." WHERE email_marketing_list_id!=''");

        $queue_subscribe = $this->db->GetAll("SELECT mq.list_id, u.user_email FROM ".T_EMAIL_MARKETING_QUEUE." mq
        INNER JOIN ".T_USERS." u ON mq.user_id=u.id WHERE action='subscribe'
        ORDER BY date_queued ASC LIMIT ?",array($amount));

        $queue_processed = array();
        foreach($queue_subscribe AS $email) {
            $queue_processed[$email['list_id']][] = $email['user_email'];
        }

        foreach($queue_processed AS $list_id=>$emails) {
            $this->batchSubscribe($lists[$list_id],$emails);
        }

        $queue_unsubscribe = $this->db->GetAll("SELECT mq.list_id, u.user_email FROM ".T_EMAIL_MARKETING_QUEUE." mq
        INNER JOIN ".T_USERS." u ON mq.user_id=u.id WHERE action='unsubscribe'
        ORDER BY date_queued ASC LIMIT ?",array($amount));

        $queue_processed = array();
        foreach($queue_subscribe AS $list_id=>$email) {
            $queue_processed[$email['list_id']][] = $email['user_email'];
        }

        foreach($queue_processed AS $list_id=>$emails) {
            $this->batchUnsubscribe($lists[$list_id],$emails);
        }
    }
}
?>