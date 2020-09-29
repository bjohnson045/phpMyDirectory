<?php
/**
* Class Ratings
* Listing Ratings
*/
class Ratings extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Ratings Constructor
    * @param object $PMDR Registry
    * @return void;
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_RATINGS;
    }

    /**
    * Get number of ratings by listing ID
    * @param integer $listing_id Listing ID
    * @return integer Number of votes
    */
    function totalCount($listing_id) {
        return $this->db->GetOne("SELECT votes FROM ".T_LISTINGS." WHERE selector=?",array($listing_id));
    }

    /**
    * Delete all ratings with specific listing ID
    * @param integer $id Listing ID
    * @return void
    */
    function deleteByListingID($id) {
        $this->db->Execute("DELETE FROM ".T_RATINGS." WHERE listing_id=?",array($listing_id));
    }

    /**
    * Get rating categories
    * @return array
    */
    function getCategories() {
        return $this->db->GetAll("SELECT * FROM ".T_RATINGS_CATEGORIES." ORDER BY ordering, title");
    }

    /**
    * Insert rating and update listing stored rating
    * @param array $data rating data
    * @return boolean true if successfully inserted
    */
    function insert($data) {
        $data['ip_address'] = get_ip_address();
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        parent::insert($data);
        $rating_id = $this->db->Insert_ID();
        // When a new rating is created, trigger an update on the listing.
        $this->PMDR->get('Listings')->updateRating($data['listing_id']);
        return $rating_id;
    }

    /**
    * Update a rating
    * @param array $data
    * @param int $id
    */
    function update($data,$id) {
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        parent::update($data,$id);
        // After a rating is updated, trigger an update on the listing.
        $this->PMDR->get('Listings')->updateRating($data['listing_id']);
    }

    /**
    * Delete a rating
    * @param int $id
    */
    function delete($id) {
        $listing_id = $this->db->GetOne("SELECT listing_id FROM ".T_RATINGS." WHERE id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_RATINGS." WHERE id=?",array($id));
        $this->PMDR->get('Listings')->updateRating($listing_id);
    }

    /**
    * Check if a user (by IP address) has voted for a listing
    * @param integer $listing_id Listing ID
    * @return integer If positive, user has rated listing before
    */
    function hasVoted($listing_id, $user_id = false) {
        if($user_id) {
            return $this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_RATINGS." WHERE user_id=? AND listing_id=?",array($user_id,$listing_id));
        } else {
            return $this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_RATINGS." WHERE listing_id=? AND ip_address=?",array($listing_id,get_ip_address()));
        }
    }

    /**
    * Print static rating without AJAX features
    * @param integer $stars Rating to show
    * @return string HTML output
    */
    function printRatingStatic($stars_count=0) {
        $stars = array();
        for($x=1; $x <= 5; $x++) {
            $stars[$x] = ($x <= $stars_count);
        }
        if(PMD_SECTION == 'admin') {
            $template = $this->PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_stars.tpl');
        } else {
            $template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/stars.tpl');
        }
        $template->set('stars',$stars);
        return $template->render();
    }
}
?>