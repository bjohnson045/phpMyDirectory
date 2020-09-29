<?php
class Listings_Reviews_New_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_reviews_new_number'));
        }
        if($limit) {
            $block_listings_reviews_new_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_listings_reviews_new.tpl');
            $block_listings_reviews_new_template->cache_id = 'block_listings_reviews_new';
            $block_listings_reviews_new_template->expire = 900;
            if(!$block_listings_reviews_new_template->isCached()) {
                $results = $this->db->GetAll("SELECT r.id, r.listing_id, r.review, r.date, r.title, l.friendly_url, l.title AS listing_title, ra.rating FROM ".T_REVIEWS." r INNER JOIN ".T_LISTINGS." l ON r.listing_id=l.id INNER JOIN ".T_RATINGS." ra ON r.rating_id=ra.id WHERE r.status='active' ORDER BY r.date DESC LIMIT ?",array(intval($limit)));
                if(is_array($results) AND sizeof($results) > 0) {
                    foreach($results as $key=>$value) {
                        $results[$key]['url'] = BASE_URL.'/listing_reviews.php?review_id='.$value['id'];
                        $results[$key]['date'] = $this->PMDR->get('Dates_Local')->formatDate($value['date']);
                        $results[$key]['listing_url'] = $this->PMDR->get('Listings')->getURL($value['listing_id'],$value['friendly_url']);
                        $results[$key]['rating_static'] = $this->PMDR->get('Ratings')->printRatingStatic($value['rating']);
                        $results[$key]['review'] = Strings::limit_words($value['review'],50);
                    }
                }
                $block_listings_reviews_new_template->set('results',$results);
            }
            return $block_listings_reviews_new_template;
        }
    }
}
?>