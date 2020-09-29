<?php if($yelp_reviews AND $yelp_reviews['review_count'] > 0) { ?>
    <h2>Yelp Reviews</h2>
    <p><a href="<?php echo $yelp_reviews['url']; ?>"><?php echo $yelp_reviews['name']; ?></a> <img src="<?php echo $yelp_reviews['rating_img_url_small']; ?>" alt="" /> (<?php echo $yelp_reviews['review_count']; ?>)</p>
    <?php foreach($yelp_reviews['reviews'] AS $yelp_review) { ?>
        <p><img src="<?php echo $yelp_review['rating_image_small_url']; ?>" alt="" /> (<?php echo $yelp_review['date']; ?>)</p>
        <p><?php echo $yelp_review['excerpt']; ?></p>
    <?php } ?>
    <p><a href="http://www.yelp.com"><img src="<?php echo BASE_URL; ?>/modules/plugins/yelp_reviews/yelp_reviews.gif" alt="" /></a></p>
<?php } ?>