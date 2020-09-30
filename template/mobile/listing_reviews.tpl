<h3 style="margin: 0 0 25px 0"><?php echo $this->escape($listing['title']); ?></h3>
<!--
<?php if(!$logged_in) { ?>
    <span style="color: red; font-weight: bold;"><?php echo $lang['public_listing_reviews_note']; ?>: </span><a href="<?php echo $login_url; ?>"><?php echo $lang['public_listing_reviews_login']; ?></a> <?php echo $lang['public_listing_reviews_login2']; ?><br /><br />
<?php } ?>
-->
<?php if(sizeof($reviews) > 0) { ?>
    <ul data-role="listview">
    <?php foreach($reviews AS $review) { ?>
        <?php echo $review; ?>
    <?php } ?>
    </ul>
<?php } ?>
<?php echo $page_navigation; ?>