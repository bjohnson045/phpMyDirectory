<h1><?php echo $lang['admin_index_summary']; ?></h1>
<div id="admin_index_statistics">
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="admin_users.php?action=add" class="btn btn-default btn-xs pull-right"><i class="fa fa-plus"></i></a>
                    <a href="admin_users.php?action=search" class="btn btn-default btn-xs pull-right"><i class="fa fa-search"></i></a>
                    <?php echo $lang['admin_index_users']; ?>
                </div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php"><?php echo $lang['admin_index_users']; ?><span class="badge <?php if($total_users > 0) { ?>badge-success<?php } ?>"><?php echo $total_users; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?group_id=5"><?php echo $lang['admin_index_users_email_unconfirmed']; ?><span class="badge <?php if($users_unconfirmed_email > 0) { ?> badge-info<?php } ?>"><?php echo $users_unconfirmed_email; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?status=no_order"><?php echo $lang['admin_index_users_no_order']; ?><span class="badge <?php if($users_without_order > 0) { ?> badge-info<?php } ?>"><?php echo $users_without_order; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?status=this_week"><?php echo $lang['admin_index_users_this_week']; ?><span class="badge <?php if($users_this_week > 0) { ?> badge-success<?php } ?>"><?php echo $users_this_week; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_contact_requests.php?status=pending"><?php echo $lang['admin_index_users_pending_contact_requests']; ?><span class="badge <?php if($users_pending_contact_requests > 0) { ?> badge-important<?php } ?>"><?php echo $users_pending_contact_requests; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_cancellations.php"><?php echo $lang['admin_index_cancellations']; ?><span class="badge<?php if($order_cancellations > 0) { ?> badge-important<?php } ?>"><?php echo $order_cancellations; ?></span></a>
                </ul>
            </div>
        </div>
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="admin_orders_add.php" class="btn btn-default btn-xs pull-right"><i class="fa fa-plus"></i></a>
                    <a href="admin_orders.php?action=search" class="btn btn-default btn-xs pull-right"><i class="fa fa-search"></i></a>
                    <?php echo $lang['admin_index_orders']; ?>
                </div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?status=active"><?php echo $lang['admin_index_orders_active']; ?><span class="badge <?php if($order_statuses['active'] > 0) { ?> badge-success<?php } ?>"><?php echo $order_statuses['active']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?status=completed"><?php echo $lang['admin_index_orders_completed']; ?><span class="badge <?php if($order_statuses['completed'] > 0) { ?> badge-success<?php } ?>"><?php echo $order_statuses['completed']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?status=suspended"><?php echo $lang['admin_index_orders_suspended']; ?><span class="badge <?php if($order_statuses['suspended'] > 0) { ?> badge-important<?php } ?>"><?php echo $order_statuses['suspended']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?status=pending"><?php echo $lang['admin_index_orders_pending']; ?><span class="badge <?php if($order_statuses['pending'] > 0) { ?> badge-important<?php } ?>"><?php echo $order_statuses['pending']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?status=fraud"><?php echo $lang['admin_index_orders_fraud']; ?><span class="badge <?php if($order_statuses['fraud'] > 0) { ?> badge-info<?php } ?>"><?php echo $order_statuses['fraud']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?status=canceled"><?php echo $lang['admin_index_orders_canceled']; ?><span class="badge"><?php echo $order_statuses['canceled']; ?></span></a>
                </ul>
            </div>
        </div>
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
            <div class="panel-heading">
                <a href="admin_invoices.php?action=add" class="btn btn-default btn-xs pull-right"><i class="fa fa-plus"></i></a>
                <a href="admin_invoices.php?action=search" class="btn btn-default btn-xs pull-right"><i class="fa fa-search"></i></a>
                <?php echo $lang['admin_index_invoices']; ?>
            </div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?status=due_today"><?php echo $lang['admin_index_invoices_due_today']; ?><span class="badge <?php if($invoices_due_today > 0) { ?> badge-info<?php } ?>"><?php echo $invoices_due_today; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?status=paid"><?php echo $lang['admin_index_invoices_paid']; ?><span class="badge <?php if($invoice_statuses['paid'] > 0) { ?> badge-success<?php } ?>"><?php echo $invoice_statuses['paid']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?status=unpaid"><?php echo $lang['admin_index_invoices_unpaid']; ?><span class="badge <?php if($invoice_statuses['unpaid'] > 0) { ?> badge-important<?php } ?>"><?php echo $invoice_statuses['unpaid']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?status=canceled"><?php echo $lang['admin_index_invoices_canceled']; ?><span class="badge <?php if($invoice_statuses['canceled'] > 0) { ?> badge-important<?php } ?>"><?php echo $invoice_statuses['canceled']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?status=overdue"><?php echo $lang['admin_index_invoices_overdue']; ?><span class="badge <?php if($invoices_overdue > 0) { ?> badge-important<?php } ?>"><?php echo $invoices_overdue; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_transactions.php"><?php echo $lang['admin_index_transactions']; ?><span class="badge <?php if($transactions > 0) { ?> badge-important<?php } ?>"><?php echo $transactions; ?></span></a>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
            <div class="panel-heading">
                <a href="admin_orders_add.php?type=listing_membership" class="btn btn-default btn-xs pull-right"><i class="fa fa-plus"></i></a>
                <a href="admin_listings_search.php" class="btn btn-default btn-xs pull-right"><i class="fa fa-search"></i></a>
                <?php echo $lang['admin_index_listings']; ?>
            </div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_listings.php"><?php echo $lang['admin_index_listings']; ?><span class="badge <?php if($total_listings > 0) { ?> badge-success<?php } ?>" ><?php echo $total_listings; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_listings_suggestions.php"><?php echo $lang['admin_index_listings_suggestions']; ?><span class="badge <?php if($listing_suggestions > 0) { ?> badge-important<?php } ?>"><?php echo $listing_suggestions; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_listings_claims.php"><?php echo $lang['admin_index_listings_claims']; ?><span class="badge <?php if($listing_claims > 0) { ?> badge-important<?php } ?>"><?php echo $listing_claims; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_updates.php"><?php echo $lang['admin_index_listings_updates']; ?><span class="badge <?php if($pending_updates > 0) { ?> badge-important<?php } ?>"><?php echo $pending_updates; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_listings.php?coordinates=no"><?php echo $lang['admin_index_listings_no_coordinates']; ?><span class="badge <?php if($listings_without_coordinates > 0) { ?> badge-important<?php } ?>"><?php echo $listings_without_coordinates; ?></span></a>
                </ul>
            </div>
        </div>
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $lang['admin_index_reviews']; ?></div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_reviews.php?status=active"><?php echo $lang['admin_index_reviews_active']; ?><span class="badge <?php if($review_statuses['active'] > 0) { ?> badge-success<?php } ?>"><?php echo $review_statuses['active']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_reviews.php?status=pending"><?php echo $lang['admin_index_reviews_pending']; ?><span class="badge <?php if($review_statuses['pending'] > 0) { ?> badge-important<?php } ?>"><?php echo $review_statuses['pending']; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_ratings.php"><?php echo $lang['admin_index_reviews_ratings']; ?><span class="badge <?php if($total_ratings > 0) { ?> badge-success<?php } ?>"><?php echo $total_ratings; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_reviews_comments.php?status=pending"><?php echo $lang['admin_index_reviews_comments_pending']; ?><span class="badge <?php if($pending_comments > 0) { ?>badge-important<?php } ?>"><?php echo $pending_comments; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_reviews.php"><?php echo $lang['admin_index_reviews_quality_votes']; ?><span class="badge"><?php echo $quality_votes; ?></span></a>
                </ul>
            </div>
        </div>
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $lang['admin_index_other']; ?></div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_categories.php"><?php echo $lang['admin_index_other_categories']; ?><span class="badge"><?php echo $total_categories; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_locations.php"><?php echo $lang['admin_index_other_locations']; ?><span class="badge"><?php echo $total_locations; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_email_queue.php"><?php echo $lang['admin_index_other_emails']; ?><span class="badge<?php if($email_queue > 0) { ?> badge-info<?php } ?>"><?php echo $email_queue; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_email_queue.php?moderate=1"><?php echo $lang['admin_index_moderated_emails']; ?><span class="badge<?php if($email_queue_moderate > 0) { ?> badge-important<?php } ?>"><?php echo $email_queue_moderate; ?></span></a>

                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_index_events']; ?></div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_events.php"><?php echo $lang['admin_index_events']; ?><span class="badge <?php if($events > 0) { ?> badge-success<?php } ?>" ><?php echo $events; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_events.php?status=pending"><?php echo $lang['admin_index_events_pending']; ?><span class="badge <?php if($events_pending > 0) { ?> badge-danger<?php } ?>"><?php echo $events_pending; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_events.php?date_start=<?php echo date('Y-m-d'); ?>&date_end=<?php echo date('Y-m-d',strtotime('+7 days')); ?>"><?php echo $lang['admin_index_events_upcoming']; ?><span class="badge <?php if($events_upcoming > 0) { ?> badge-info<?php } ?>"><?php echo $events_upcoming; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_events.php?"><?php echo $lang['admin_index_events_rsvps']; ?><span class="badge <?php if($events_rsvps > 0) { ?> badge-info<?php } ?>"><?php echo $events_rsvps; ?></span></a>
                </ul>
            </div>
        </div>
        <?php if(ADDON_BLOG) { ?>
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_index_blog']; ?></div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_blog.php"><?php echo $lang['admin_index_blog_posts']; ?><span class="badge <?php if($blog > 0) { ?> badge-success<?php } ?>" ><?php echo $blog; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_blog.php?status=pending"><?php echo $lang['admin_index_blog_posts_pending']; ?><span class="badge<?php if($blog_pending > 0) { ?> badge-important<?php } ?>"><?php echo $blog_pending; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_blog_comments.php?status=pending"><?php echo $lang['admin_index_blog_comments_pending']; ?><span class="badge<?php if($blog_comments_pending > 0) { ?> badge-important<?php } ?>"><?php echo $blog_comments_pending; ?></span></a>
                </ul>
            </div>
        </div>
        <?php } ?>
        <div class="col-md-8 col-lg-6">
            <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_index_other_content']; ?></div>
                <ul class="list-group">
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_images.php"><?php echo $lang['admin_index_images']; ?><span class="badge"><?php echo $images; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_documents.php"><?php echo $lang['admin_index_documents']; ?><span class="badge"><?php echo $documents; ?></span></a>
                    <a class="list-group-item" href="<?php echo BASE_URL_ADMIN; ?>/admin_classifieds.php"><?php echo $lang['admin_index_classifieds']; ?><span class="badge"><?php echo $classifieds; ?></span></a>
                </ul>
            </div>
        </div>
    </div>
</div>