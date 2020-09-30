<?php
/**
* Redirects class
* Handles URL changes when renaming is done to preserve URL rank
*/
class Redirects extends TableGateway {
    /**
    * PMD Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;

    /**
    * Redirects constructor
    * @param mixed $PMDR
    * @return Redirects
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_REDIRECTS;
    }

    /**
    * Get a URL based on the hash and type
    * @param string $hash
    * @param string $type
    * @return string URL
    */
    function getURL($hash, $type) {
        return $this->db->GetOne("SELECT url FROM ".T_REDIRECTS." WHERE url_hash=? AND type=?",array($hash,$type));
    }

    /**
    * Get a redirect by type and type ID
    *
    * @param string $type
    * @param int $type_id
    * @return mixed boolean false on failure, string URL on success
    */
    function getURLByID($type, $type_id){
        $record = $this->db->GetRow("SELECT * FROM ".T_REDIRECTS." WHERE type=? AND type_id=?",array($type,$type_id));
        if($record) {
            if($record['type_new'] == 'listing') {
                $listing = $this->db->GetRow("SELECT id, friendly_url FROM ".T_LISTINGS." WHERE id=?",array($record['type_id_new']));
                if($listing) {
                    return $this->PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
                }
            }
        }
        return false;
    }

    /**
    * Insert a redirect
    * @param string $type
    * @param int $type_id
    * @param string $url
    */
    function insert($data) {
        $hash = md5($data['url']);
        return $this->db->Execute("REPLACE INTO ".T_REDIRECTS." (type,type_id,url,url_hash,date_redirected) VALUES (?,?,?,?,NOW())",array(array($data['type'],$data['type_id'],$data['url'],$hash)));
    }

    /**
    * Insert a redirect using an ID
    * @param string $type
    * @param int $type_id
    * @param string $type_id
    * @param int $type_id_new
    */
    function insertID($type, $type_id, $type_new, $type_id_new) {
        return $this->db->Execute("REPLACE INTO ".T_REDIRECTS." (type,type_id,type_new,type_id_new,date_redirected) VALUES (?,?,?,?,NOW())",array(array($type,$type_id,$type_new,$type_id_new)));
    }
}
?>