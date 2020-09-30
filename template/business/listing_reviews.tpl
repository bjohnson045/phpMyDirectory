<div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
    <h1><span itemprop="itemreviewed"><?php echo $this->escape($title); ?></span></h1>
    <a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
    <h2><?php echo $lang['public_listing_reviews']; ?></h2>
    <?php if(!$logged_in) { ?>
        <p><span class="text-danger"><?php echo $lang['public_listing_reviews_note']; ?>: </span><a href="<?php echo $login_url; ?>"><?php echo $lang['public_listing_reviews_login']; ?></a> <?php echo $lang['public_listing_reviews_login2']; ?></p>
    <?php } ?>
    <div class="row">
        <div class="col-lg-2 col-md-3 col-sm-4 hidden-xs">
            <?php for($x=5; $x > 0; $x--) { ?>
            <div class="progress-rating-container">
                <div class="pull-left" style="padding-right: 5px; clear: left;"><?php echo $x; ?> <?php echo $lang['public_listing_reviews_star']; ?>: </div>
                <div class="progress progress-rating">
                    <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $reviews_counts[$x]['meter_width']; ?>%;">
                        <span><?php echo $reviews_counts[$x]['count']; ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
            <p>
                <strong><?php echo $lang['public_listing_reviews_average']; ?></strong><br>
                <small>
                <span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">
                    <span itemprop="average"><?php echo $average; ?></span> <?php echo $lang['public_listing_reviews_of']; ?> <span itemprop="best">5</span>
                </span>
                <?php echo $lang['public_listing_reviews_from']; ?> <span itemprop="votes"><?php echo $total_reviews; ?></span> <?php echo $lang['public_listing_reviews_reviews_lower']; ?>.
                </small>
            </p>
            <p class="listing_reviews_average"><?php echo $average_stars; ?></p>
            <p><a href="<?php echo $reviews_add_url; ?>" class="btn btn-default"><span class="fa fa-plus"></span> <?php echo $lang['public_listing_reviews_add']; ?></a></p>
        </div>
        <?php if($categories) { ?>
        <div class="col-lg-7 col-md-3 col-sm-4 col-xs-6">
            <p><strong><?php echo $lang['public_listing_reviews_categories_average']; ?></strong></p>
            <?php foreach($categories AS $category) { ?>
                <div class="clear-left">
                    <div class="pull-left" style="margin-right: 5px;"><?php echo $category['average_static']; ?></div>
                    <div><?php echo $category['title']; ?></div>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>
<?php foreach($reviews AS $review) { ?>
    <hr size="1">
    <?php echo $review; ?>
<?php } ?>
<div class="text-center">
    <?php echo $page_navigation; ?>
</div>
