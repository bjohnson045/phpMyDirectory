<?php
$PMDR->get('Plugins')->add_hook('listing_end', 'yelpReviewsListing', 10);

function yelpReviewsListing() {
	global $PMDR, $template_content, $listing;

    $yelp_reviews_template = $PMDR->getNew('Template',PMDROOT.'/modules/plugins/yelp_reviews/yelp_reviews.tpl');

    if($PMDR->get('Yelp') AND !empty($listing['phone'])) {
        if($yelp_reviews = $PMDR->get('Yelp')->getReviewsByPhone($listing['phone'])) {
            $yelp_reviews_template->set('yelp_reviews',$yelp_reviews);
        }
        unset($yelp_reviews);
    }

    $template_content->set('yelp_reviews',$yelp_reviews_template);
}
?>