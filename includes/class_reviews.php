<?php
/**
* Class Reviews
* Listing reviews
*/
class Reviews extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Reviews Constructor
    * @param object $this->PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_REVIEWS;
    }

    /**
    * Get a single review from the database
    * @param int $id Review ID
    * @return array Array of review data
    */
    function getRow($id) {
        if($ratings_categories = $this->db->GetCol("SELECT id FROM ".T_RATINGS_CATEGORIES)) {
            $categories_sql = ',rt.category_'.implode(',rt.category_',$ratings_categories);
        }
        return $this->db->GetRow("SELECT r.*, rt.rating $categories_sql FROM ".T_REVIEWS." r LEFT JOIN ".T_RATINGS." rt ON r.rating_id=rt.id WHERE r.id=?",array($id));
    }

    /**
    * Update review
    * @param array $data Review data
    * @return void
    */
    function update($data, $id) {
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();

        if(isset($data['rating']) and $data['rating'] != '') {
            // This will trigger a listing update, but we will still need to explicitly call a listing update
            // in case the rating data was not set. (Perhaps just the status was changed.)
            if(!$data['rating_id']) {
                $data['ip_address'] = get_ip_address();
                $data['rating_id'] = $this->PMDR->get('Ratings')->insert($data);
                unset($data['ip_address']);
            } else {
                $this->PMDR->get('Ratings')->update($data,$data['rating_id']);
            }
        }
        $result = parent::update($data, $id);

        // Now that the review changes have been saved, update the listing's rating again.
        $this->PMDR->get('Listings')->updateRating($data['listing_id']);
        return $result;
    }

    /**
    * Insert Review
    * A rating is also inserted if one is included
    * @param array $data Review and/or rating data
    * @return void
    */
    function insert($data) {
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        $return = parent::insert($data);
        $this->PMDR->get('Listings')->updateRating($data['listing_id']);
        return $return;
    }

    /**
    * Delete a review
    * @param int $id
    * @return void
    */
    function delete($id) {
        $review = $this->db->GetRow("SELECT id, rating_id, listing_id FROM ".T_REVIEWS." WHERE id=?",array($id));
        $this->PMDR->get('Ratings')->delete($review['rating_id']);
        parent::delete($id);
        $this->PMDR->get('Listings')->updateRating($review['listing_id']);
    }

    /**
    * Approve Review
    * @param integer $id Review ID
    * @return void
    */
    function approve($id) {
        $listing_id = $this->db->GetOne("SELECT listing_id FROM ".T_REVIEWS." WHERE id=?",array($id));
        $listing_user_id = $this->db->GetOne("SELECT user_id FROM ".T_LISTINGS." WHERE id=?",array($listing_id));
        $this->db->Execute("UPDATE ".T_REVIEWS." SET status='active' WHERE id=?",array($id));
        $this->PMDR->get('Email_Templates')->send('listing_review_submitted_notification',array('to'=>$listing_user_id,'review_id'=>$id));
        $this->PMDR->get('Listings')->updateRating($listing_id);
    }

    /**
    * Get a review assigned to the review template file
    * @param array $review Review array
    * @param array $ratings_categories Ratings categories array
    * @param boolean $comments Get the review comments or not
    * @return object Template object
    */
    function getReviewTemplate($review, $ratings_categories, $comments = false, $listing_user_id = null) {
        if(!is_array($ratings_categories)) {
            $ratings_categories = array();
        }

        $template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/blocks/listing_review.tpl');
        $template->set('id',$review['id']);
        $template->set('helpful_count',$review['helpful_count']);
        $template->set('helpful_total',$review['helpful_total']);
        $template->set('rating',$review['rating']);
        $template->set('comment_count',$review['comment_count']);
        $template->set('share',$this->PMDR->get('Sharing')->getHTML(BASE_URL.'/listing_reviews.php?review_id='.$review['id'],$review['title'],null,true));

        foreach($ratings_categories AS &$category) {
            $category['rating_static'] = $this->PMDR->get('Ratings')->printRatingStatic($review['category_'.$category['id']]);
        }
        $template->set('categories',$ratings_categories);

        // Remove new lines from the actual review
        $template->set('review',Strings::nl2br_replace($review['review']));
        // Get the stars static HTML
        $template->set('rating_static',$this->PMDR->get('Ratings')->printRatingStatic($review['rating']));
        // Shorten the title
        if($review['title'] == '') {
            $template->set('title',Strings::limit_characters($review['review'],50).'..');
        } else {
            $template->set('title',$review['title']);
        }
        // Format the date
        $template->set('date',$this->PMDR->get('Dates_Local')->formatDate($review['date']));
        $template->set('time',$this->PMDR->get('Dates_Local')->formatTime($review['date']));
        // Set the login name for the review
        if(!is_null($review['user_id'])) {
            $template->set('login',$review['user_name_formatted']);
        } else {
            if($review['name'] == '') {
                $template->set('login',$this->PMDR->getLanguage('anonymous'));
            } else {
                $template->set('login',$review['name']);
            }
        }
        // Get the profile image of the user
        if(!is_null($review['user_id']) AND $profile_image_url = get_file_url_cdn(PROFILE_IMAGES_PATH.$review['user_id'].'.*')) {
            $template->set('profile_image_url',$profile_image_url);
        }
        // Set up the comment and helpfulness URLs
        $template->set('comment_url',BASE_URL.'/listing_reviews_comments.php?id='.$review['id']);

        if($comments) {
            $comments = $this->db->GetAll("SELECT c.id, c.user_id, c.date, c.comment, u.login FROM ".T_REVIEWS_COMMENTS." c INNER JOIN ".T_USERS." u ON c.user_id=u.id WHERE review_id=? AND status='active'",array($review['id']));
            foreach($comments as $comment_key=>$comment) {
                $comments[$comment_key]['date'] = $this->PMDR->get('Dates_Local')->formatDateTime($comment['date']);
                if(!is_null($listing_user_id) AND $comment['user_id'] == $listing_user_id) {
                    $comments[$comment_key]['owner'] = true;
                }
            }
            $template->set('comments',$comments);
        }
        return $template;
    }

}
?>