<?php
/**
* Logger Class
*/
class Logger {
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
    * Logger constructor
    * @param object $PMDR
    * @return Logger
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Enter a log message
    * @param string $action_type The type of action
    * @param mixed $action Single string action or an array of actions
    */
    public function log($action_type, $action) {
        if(is_array($action)) {
            $action = implode("\n",$action);
        }
        $user_id = $this->PMDR->get('Session')->get('admin_id') ? $this->PMDR->get('Session')->get('admin_id') : $this->PMDR->get('Session')->get('user_id');

        if($ip_address = get_ip_address()) {
            $host = gethostbyaddr($ip_address);
        }
        if(!isset($host) OR !$host) {
            $host = '';
        }
        $this->db->Execute("INSERT INTO ".T_LOG." (user_id, ip_address, hostname, action_date, action_type, action) VALUES (?,?,?,NOW(),?,?)",array($user_id,$ip_address,$host,$action_type,$action));
    }

    /**
    * Get log count
    * @return int Count
    */
    public function getLogCount() {
        return $this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_LOG);
    }

    /**
    * Get log entries by offset and count
    * @param int $offset Limit offset
    * @param int $count Limit count
    * @return array Log records
    */
    public function getLog($offset, $count) {
        return $this->db->GetAll("SELECT * FROM ".T_LOG." ORDER BY id DESC LIMIT ?,?",array($offset,$count));
    }
}
?>