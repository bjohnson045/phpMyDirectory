<ul class="star-rating">
    <li class="current-rating" style="width:<?php echo $rating*16; ?>px;"><?php echo $rating; ?> Stars.</li>
    <?php if($rating_allowed) { ?>
        <li><a href="#" title="1 <?php echo $lang['public_listing_stars']; ?>" class="one-star">1</a></li>
        <li><a href="#" title="2 <?php echo $lang['public_listing_stars']; ?>" class="two-stars">2</a></li>
        <li><a href="#" title="3 <?php echo $lang['public_listing_stars']; ?>" class="three-stars">3</a></li>
        <li><a href="#" title="4 <?php echo $lang['public_listing_stars']; ?>" class="four-stars">4</a></li>
        <li><a href="#" title="5 <?php echo $lang['public_listing_stars']; ?>" class="five-stars">5</a></li>
    <?php } ?>
</ul>