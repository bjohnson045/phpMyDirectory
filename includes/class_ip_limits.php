<?php
/**
* IP Limits Class
*/
class IP_Limits extends TableGateway {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Database
    * @var Database
    */
    var $db;

    /**
    * IP Limits Constructor
    * @param object $PMDR
    * @return Zones
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_IP_LIMIT;
    }

    /**
    * Insert a limit
    *
    * @param array $data
    */
    function insert($data) {
        $this->db->Execute("INSERT INTO ".T_IP_LIMIT." (ip_address,type,date) VALUES (?,?,NOW())",array(get_ip_address(),$data['type']));
    }

    /**
    * Delete old limits according to a number of days
    *
    * @param int $days
    */
    function deleteOld($days) {
        $this->db->Execute("DELETE FROM ".T_IP_LIMIT." WHERE date < DATE_SUB(NOW(), INTERVAL ? DAY)",array(intval($days)));
    }

    /**
    * Get the difference between a recent limit entry and the current date
    *
    * @param string $type
    * @param int $seconds
    * @return int Number of seconds
    */
    function getSecondsDifference($type,$seconds) {
        return $this->db->GetOne("SELECT TIMESTAMPDIFF(SECOND,date,NOW()) FROM ".T_IP_LIMIT." WHERE ip_address=? AND type=? AND date > DATE_SUB(NOW(), INTERVAL ? SECOND) ORDER BY date DESC LIMIT 1",array(get_ip_address(),$type,intval($seconds)));
    }

    /**
    * Check if a limit is over an hour limit
    *
    * @param string $type
    * @param int $limit
    * @param int $hours
    */
    function isOverHourLimit($type,$limit,$hours) {
        $count = $this->db->GetOne("SELECT COUNT(*) FROM ".T_IP_LIMIT." WHERE type=? AND ip_address=? AND date > DATE_SUB(NOW(), INTERVAL ? HOUR)",array($type,get_ip_address(),intval($hours)));
        return ($limit <= $count);
    }
}
?>