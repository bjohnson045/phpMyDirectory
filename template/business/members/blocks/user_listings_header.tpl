<h1><?php echo $listing_title; ?></h1>
<div class="btn-toolbar" style="margin-bottom: 10px">
    <div class="btn-group">
        <a href="user_listings_summary.php?id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'summary') { ?> active<?php } ?>"><?php echo $lang['user_general_summary']; ?></a>
    </div>
    <div class="btn-group">
        <a href="user_orders_view.php?id=<?php echo $order_id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'order') { ?> active<?php } ?>"><?php echo $lang['user_general_order_information']; ?></a>
    </div>
    <div class="btn-group">
        <a href="<?php echo BASE_URL; ?>/listing.php?id=<?php echo $id; ?>" class="btn btn-default btn-sm" target="_blank"><?php echo $lang['user_general_view_public_listing']; ?></a>
    </div>
    <div class="btn-group">
        <a href="user_listings.php?action=edit&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'edit') { ?> active<?php } ?>"><?php echo $lang['user_general_edit_listing']; ?></a>
    </div>
    <div class="btn-group">
        <a href="user_listings_statistics.php?listing_id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'statistics') { ?> active<?php } ?>"><?php echo $lang['user_general_statistics']; ?></a>
    </div>
</div>
<div class="btn-toolbar">
    <?php if($listing['locations_limit']) { ?>
    <div class="btn-group">
        <a href="user_listings_locations.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'listing_locations') { ?> active<?php } ?>"><?php echo $lang['user_general_listings_locations']; ?></a>
        <a href="user_listings_locations.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['images_limit']) { ?>
    <div class="btn-group">
        <a href="user_images.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'images') { ?> active<?php } ?>"><?php echo $lang['user_general_images']; ?></a>
        <a href="user_images.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['documents_limit']) { ?>
    <div class="btn-group">
        <a href="user_documents.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'documents') { ?> active<?php } ?>"><?php echo $lang['user_general_documents']; ?></a>
        <a href="user_documents.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['classifieds_limit']) { ?>
    <div class="btn-group">
        <a href="user_classifieds.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'classifieds') { ?> active<?php } ?>"><?php echo $lang['user_general_classifieds']; ?></a>
        <a href="user_classifieds.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['events_limit']) { ?>
    <div class="btn-group">
        <a href="user_events.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'events') { ?> active<?php } ?>"><?php echo $lang['user_general_events']; ?></a>
        <a href="user_events.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['jobs_limit']) { ?>
    <div class="btn-group">
        <a href="user_jobs.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'jobs') { ?> active<?php } ?>"><?php echo $lang['user_general_jobs']; ?></a>
        <a href="user_jobs.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($config['blog_user_posts'] AND $listing['blog_posts_limit'] AND ADDON_BLOG) { ?>
    <div class="btn-group">
        <a href="user_blog.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'blog_posts') { ?> active<?php } ?>"><?php echo $lang['user_general_blog_posts']; ?></a>
        <a href="user_blog.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['banners_allow']) { ?>
    <div class="btn-group">
        <a href="user_banners.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'banners') { ?> active<?php } ?>"><?php echo $lang['user_general_banners']; ?></a>
        <a href="user_banners.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <?php } ?>
    <?php if($listing['reviews_allow']) { ?>
    <div class="btn-group">
        <a href="user_listings_reviews.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'reviews') { ?> active<?php } ?>"><?php echo $lang['user_general_reviews']; ?></a>
    </div>
    <?php } ?>
    <div class="btn-group">
        <a href="<?php echo BASE_URL; ?>/site_links.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><?php echo $lang['user_general_site_links']; ?></a>
    </div>
</div>
<h2><?php echo $title; ?></h2>