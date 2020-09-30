<script type="text/javascript">
$(document).ready(function() {
    $("#listing_delete").click(function(e) {
        return confirm(<?php echo $this->escape_js($lang['messages_confirm']); ?>);
    });
});
</script>
<h2><?php echo $lang['listing']; ?>: <?php echo $title; ?></h2>
<div class="btn-toolbar">
    <div class="btn-group">
        <a href="admin_orders_summary.php?id=<?php echo $order_id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'order') { ?> active<?php } ?>">Order Details</a>
    </div>
    <div class="btn-group">
        <a href="admin_listings.php?action=edit&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'edit') { ?> active<?php } ?>"><?php echo $lang['listing_edit']; ?></a>
    </div>
    <div class="btn-group">
        <a href="admin_listings_locations.php?listing_id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'locations') { ?> active<?php } ?>">Locations</a>
        <a href="admin_listings_locations.php?action=add&listing_id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_images.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'images') { ?> active<?php } ?>">Images</a>
        <a href="admin_images.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_documents.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'documents') { ?> active<?php } ?>">Documents</a>
        <a href="admin_documents.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_classifieds.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'classifieds') { ?> active<?php } ?>">Classifieds</a>
        <a href="admin_classifieds.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_events.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'events') { ?> active<?php } ?>">Events</a>
        <a href="admin_events.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_jobs.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'jobs') { ?> active<?php } ?>">Jobs</a>
        <a href="admin_jobs.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_blog.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'blog_posts') { ?> active<?php } ?>">Blog Posts</a>
        <a href="admin_blog.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_banners.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'banners') { ?> active<?php } ?>">Banners</a>
        <a href="admin_banners.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_reviews.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'reviews') { ?> active<?php } ?>">Reviews</a>
        <a href="admin_reviews.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_ratings.php?listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm<?php if($active == 'ratings') { ?> active<?php } ?>">Ratings</a>
        <a href="admin_ratings.php?action=add&listing_id=<?php echo $id; ?>" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a href="admin_listings_statistics.php?listing_id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-default btn-sm<?php if($active == 'statistics') { ?> active<?php } ?>">Statistics</a>
    </div>
    <div class="btn-group">
        <a id="listing_delete" href="admin_listings.php?action=delete&id=<?php echo $id; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-sm btn-danger">Delete</a>
    </div>
</div>