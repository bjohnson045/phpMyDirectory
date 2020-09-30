<?php
/**
* Listing Claims
*/
class Listings_Claims extends TableGateway{
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
    * Listing claims constructor
    * @param object $PMDR
    * @return Listings_Claims
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_LISTINGS_CLAIMS;
    }

    /**
    * Clear all listing claims
    * @return void
    */
    function clear() {
        $this->db->Execute("TRUNCATE ".T_LISTINGS_CLAIMS);
    }

    /**
    * Insert listing claim
    * @param array $data Listing claim data
    * @return void
    */
    function insert($data) {
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        parent::insert($data);
    }

    /**
    * Claim a listing
    *
    * @param int $id Listing ID to claim
    * @param int $user_id User ID claiming
    */
    function claim($id,$user_id = null) {
        $claim = $this->db->GetRow("SELECT * FROM ".T_LISTINGS_CLAIMS." WHERE id=?",array($id));
        // If we don't pass in a user ID, get the one from the claim DB
        if(is_null($user_id)) {
            $user_id = $claim['user_id'];
        }
        // Mark the listing as claimed
        $this->db->Execute("UPDATE ".T_LISTINGS." SET claimed=1 WHERE id=?",array($claim['listing_id']));
        // Change the user for this listing
        $this->PMDR->get('Listings')->changeUser($claim['listing_id'],$user_id);
        // Delete all claims for this listing, including this claim since it will share the listing ID
        $this->db->Execute("DELETE FROM ".T_LISTINGS_CLAIMS." WHERE listing_id=?",array($claim['listing_id']));
    }
}
?>