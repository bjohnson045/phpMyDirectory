<?php
/**
* Class Reviews Quality
* Listing reviews helpfulness
*/
class Reviews_Quality extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Reviews Quality Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_REVIEWS_QUALITY;
    }

    /**
    * Insert Review Quality
    * We check if a row exists already for the user and change opinion if needed
    * @param array $data Review quality data
    * @return void
    */
    function insert($data) {
        $existing = $this->db->GetRow("SELECT * FROM ".T_REVIEWS_QUALITY." WHERE review_id=? AND user_id=?",array($data['review_id'],$data['user_id']));
        // Build and run the queries needed based on if feedback already exists
        if($existing) {
            if($existing['helpful'] != $data['helpful']) {
                $query_helpful = $data['helpful'] ? '+1' : '-1';
                $this->db->Execute("UPDATE ".T_REVIEWS_QUALITY." SET helpful=? WHERE id=?",array($data['helpful'],$existing['id']));
            }
        } else {
            if($data['helpful']) {
                $query_helpful = '+1, r.helpful_total=r.helpful_total+1';
            } else {
                $query_helpful = ',r.helpful_total=r.helpful_total+1';
            }
            $this->db->Execute("INSERT INTO ".T_REVIEWS_QUALITY." (review_id,user_id,helpful) VALUES (?,?,?)",array($data['review_id'],$data['user_id'],$data['helpful']));
        }
        // Do not run the update if none of the data has changed.
        if(isset($query_helpful)) {
            $this->db->Execute("UPDATE LOW_PRIORITY ".T_REVIEWS." r SET r.helpful_count = r.helpful_count$query_helpful WHERE r.id=?",array($data['review_id']));
        }
    }
}
?>