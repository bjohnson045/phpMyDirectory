<?php
/**
* Class Reviews Comments
* Listing reviews comments
*/
class Reviews_Comments extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Reviews Comments Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_REVIEWS_COMMENTS;
    }

    /**
    * Insert a review comment
    * @param array $data
    * @return resource
    */
    function insert($data) {
        if(!isset($data['status'])) {
            $data['status'] = $this->PMDR->getConfig('reviews_comments_status');
        }
        if($data['status'] == 'active') {
            $this->db->Execute("UPDATE ".T_REVIEWS." SET comment_count=comment_count+1");
        }
        if(!isset($data['date'])) {
            $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        }
        parent::insert($data);
    }

    /**
    * Delete a review comment
    * @param int $id Comment ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_REVIEWS_COMMENTS." WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_REVIEWS." SET comment_count=comment_count-1");
    }

    /**
    * Approve a review comment
    * @param int $id Comment ID
    * @return void
    */
    function approve($id) {
        $review_id = $this->db->GetOne("SELECT review_id FROM ".T_REVIEWS_COMMENTS." WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_REVIEWS_COMMENTS." SET status='active' WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_REVIEWS." SET comment_count=comment_count+1 WHERE id=?",array($review_id));
    }

    /**
    * Unapprove a review comment
    * @param int $id Comment ID
    * @return void
    */
    function unapprove($id) {
        $review_id = $this->db->GetOne("SELECT review_id FROM ".T_REVIEWS_COMMENTS." WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_REVIEWS_COMMENTS." SET status='pending' WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_REVIEWS." SET comment_count=comment_count-1 WHERE id=?",array($review_id));
    }
}
?>