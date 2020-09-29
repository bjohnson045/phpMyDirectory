<?php
/**
* Email Schedules Class
* Sends emails based on dates/days after/before events
*/
class Email_Schedules extends TableGateway {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;

    /**
    * Email Schedules constructor
    * @param object $PMDR
    * @return Email_Schedules
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_EMAIL_SCHEDULES;
    }

    /**
    * Queue emails
    * Queues all emails based on the active schedules and their events
    */
    function queueAll() {
        $email_schedules = $this->db->GetAll("SELECT * FROM ".T_EMAIL_SCHEDULES." WHERE active=1");
        foreach($email_schedules AS $schedule) {
            switch($schedule['action']) {
                case 'user_registration_after':
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT id, 'user', id, ?, NOW() FROM ".T_USERS." WHERE DATE_ADD(DATE(created), INTERVAL ".$schedule['days']." DAY) = CURDATE()",array($schedule['email_template_id']));
                    break;
                case 'user_registration_after_no_order':
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT u.id, 'user', u.id, ?, NOW() FROM ".T_USERS." u LEFT JOIN ".T_ORDERS." o ON u.id=o.user_id WHERE o.user_id IS NULL AND DATE_ADD(DATE(created), INTERVAL ".$schedule['days']." DAY) = CURDATE()",array($schedule['email_template_id']));
                    break;
                case 'user_inactivity':
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT id, 'user', id, ?, NOW() FROM ".T_USERS." WHERE DATE_ADD(DATE(logged_last), INTERVAL ".$schedule['days']." DAY) = CURDATE()",array($schedule['email_template_id']));
                    break;
                case 'listing_creation_after':
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT user_id, 'listing', id, ?, NOW() FROM ".T_LISTINGS." WHERE DATE_ADD(DATE(date), INTERVAL ".$schedule['days']." DAY) = CURDATE()",array($schedule['email_template_id']));
                    break;
                case 'order_after':
                    $where = '';
                    if(!empty($schedule['data'])) {
                        $data = @unserialize($schedule['data']);
                        if($data AND !empty($data['pricing_ids'])) {
                            $where = ' AND pricing_id IN('.implode(',',$data['pricing_ids']).')';
                        }
                    }
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT user_id, 'order', id, ?, NOW() FROM ".T_ORDERS." WHERE DATE_ADD(DATE(date), INTERVAL ".$schedule['days']." DAY) = CURDATE() $where",array($schedule['email_template_id']));
                    break;
                case 'order_after_active':
                    $where = '';
                    if(!empty($schedule['data'])) {
                        $data = @unserialize($schedule['data']);
                        if($data AND !empty($data['pricing_ids'])) {
                            $where = ' AND pricing_id IN('.implode(',',$data['pricing_ids']).')';
                        }
                    }
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT user_id, 'order', id, ?, NOW() FROM ".T_ORDERS." WHERE DATE_ADD(DATE(date_active), INTERVAL ".$schedule['days']." DAY) = CURDATE() $where",array($schedule['email_template_id']));
                    break;
                case 'order_before_due':
                    $where = '';
                    if(!empty($schedule['data'])) {
                        $data = @unserialize($schedule['data']);
                        if($data AND !empty($data['pricing_ids'])) {
                            $where = ' AND pricing_id IN('.implode(',',$data['pricing_ids']).')';
                        }
                    }
                    $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT user_id, 'order', id, ?, NOW() FROM ".T_ORDERS." WHERE DATE_SUB(DATE(next_due_date), INTERVAL ".$schedule['days']." DAY) = CURDATE() $where",array($schedule['email_template_id']));
                    break;

            }
        }
    }
}
?>