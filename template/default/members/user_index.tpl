<p><?php echo $lang['user_index_welcome']; ?></p>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_account.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['user_edit']; ?></a>
                <h3 class="panel-title"><span class="fa fa-user"></span> <?php echo $lang['user_index_account_summary']; ?></h3>
            </div>
            <div class="panel-body">
                <div class="media">
                    <?php if($user['profile_image_url']) { ?>
                    <div class="pull-left">
                        <img class="media-object thumbnail" style="width: 100px" src="<?php echo $user['profile_image_url']; ?>">
                    </div>
                    <?php } ?>
                    <div class="media-body">
                        <h4 class="media-heading"><?php echo $this->escape($user['user_first_name']); ?> <?php echo $this->escape($user['user_last_name']); ?></h4>
                        <p>
                        <?php echo $this->escape($user['user_address1']); ?><br>
                        <?php echo $this->escape($user['user_city']); ?>, <?php echo $this->escape($user['user_state']); ?> <?php echo $this->escape($user['user_zip']); ?><br>
                        <?php echo $this->escape($user['user_country']); ?>
                        </p>
                        <?php echo $lang['user_account_phone']; ?>: <?php echo $this->escape($user['user_phone']); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if($messages) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_messages.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                <h3 class="panel-title"><span class="fa fa-envelope"></span> <?php echo $lang['user_index_recent_messages']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($messages AS $message) { ?>
                    <a class="list-group-item" href="user_messages_posts.php?message_id=<?php echo $message['id']; ?>"><?php echo $this->escape($message['title']); ?><span class="pull-right"><?php echo $message['date_sent']; ?></span></a>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        <?php if($favorites) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_favorites.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                <h3 class="panel-title"><span class="fa fa-heart text-danger"></span> <?php echo $lang['user_index_favorites']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($favorites AS $favorite) { ?>
                    <li class="list-group-item">
                        <a target="_blank" href="<?php echo $favorite['url']; ?>"><?php echo $this->escape($favorite['title']); ?></a>
                        <a class="text-danger" href="user_favorites.php?id=<?php echo $favorite['favorite_id']; ?>&action=delete"><span class="fa fa-times pull-right"></span></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="col-lg-6">
        <?php if($this->checkPermission('user_advertiser')) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php if($listings) { ?><a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a><?php } ?>
                <h3 class="panel-title"><span class="fa fa-file-text-o"></span> <?php echo $lang['user_index_my_listings']; ?></h3>
            </div>
            <?php if($listings) { ?>
            <ul class="list-group">
                <?php foreach($listings AS $listing) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <span class="label label-<?php echo $listing['status']; ?>"><?php echo $lang[$listing['status']]; ?></span><br>
                    </div>
                    <p>
                        <a href="user_listings_summary.php?action=edit&id=<?php echo $listing['id']; ?>"><?php echo $this->escape($listing['title']); ?></a> (<?php echo $lang['id']; ?>: <?php echo $listing['id']; ?>)
                    </p>
                    <p>
                        <a class="btn btn-default btn-xs" href="user_listings_summary.php?action=edit&id=<?php echo $listing['id']; ?>"><span class="fa fa-pencil"></span> <?php echo $lang['user_manage']; ?></a> <a class="btn btn-default btn-xs hidden-xs quick_statistics_link" data-id="<?php echo $listing['id']; ?>" href="#"><span class="fa fa-bar-chart-o"></span> <?php echo $lang['user_index_listings_quick_statistics']; ?></a> <a target="_blank" class="btn btn-default btn-xs hidden-xs" href="<?php echo $this->escape($listing['url']); ?>"><span class="fa fa-eye"></span> <?php echo $lang['view']; ?></a>
                    </p>
                </li>
                <div id="quick_statistics_<?php echo $listing['id']; ?>" style="display: none">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['impressions']; ?></span>
                            <?php echo $lang['user_listings_total_impressions']; ?>:
                        </li>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['search_impressions']; ?></span>
                            <?php echo $lang['user_listings_total_search_impressions']; ?>:
                        </li>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['impressions_weekly']; ?></span>
                            <?php echo $lang['user_listings_impressions_last_week']; ?>:
                        </li>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['emails']; ?></span>
                            <?php echo $lang['user_listings_emails']; ?>:
                        </li>
                        <?php if($config['statistics_click_view_email']) { ?>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['email_views']; ?></span>
                            <?php echo $lang['user_listings_email_views']; ?>:
                        </li>
                        <?php } ?>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['website_clicks']; ?></span>
                            <?php echo $lang['user_listings_website_clicks']; ?>:
                        </li>
                        <?php if($config['statistics_click_view_phone']) { ?>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['phone_views']; ?></span>
                            <?php echo $lang['user_listings_phone_views']; ?>:
                        </li>
                        <?php } ?>
                        <li class="list-group-item">
                            <span class="badge"><?php echo $listing['shares']; ?></span>
                            <?php echo $lang['user_listings_shares']; ?>:
                        </li>
                    </ul>
                </div>
                <?php } ?>
            </ul>
            <?php } else { ?>
                <div class="panel-body">
                    <a class="btn btn-success btn-lg" href="user_orders_add.php"><?php echo $lang['user_index_add_listing']; ?></a>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?php if($reviews) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_reviews.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                <h3 class="panel-title"><span class="fa fa-star"></span> <?php echo $lang['user_index_my_reviews']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($reviews AS $review) { ?>
                <li class="list-group-item">
                    <div class="pull-right"><?php echo $review['rating_static']; ?></div>
                    <a target="_blank" href="<?php echo $review['listing_url']; ?>"><?php echo $this->escape($review['listing_title']); ?></a><br>
                    <small><?php echo $this->escape($review['title']); ?></small>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        <?php if($invoices_due) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                <h3 class="panel-title"><span class="fa fa-list-alt"></span> <?php echo $lang['user_index_due_invoices']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($invoices_due AS $invoice) { ?>
                    <a class="list-group-item" href="user_invoices_pay.php?id=<?php echo $invoice['id']; ?>"><?php echo $lang['invoices_invoice']; ?> <?php echo $this->getLanguage('user_index_due_invoice',array($invoice['id'],$invoice['balance'],$invoice['date_due'])); ?></a>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        <?php if($searches) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a target="_blank" href="<?php echo BASE_URL; ?>/search.php" class="btn btn-default btn-xs pull-right"><?php echo $lang['user_index_new_search']; ?></a>
                <h3 class="panel-title"><span class="fa fa-list-alt"></span> <?php echo $lang['user_index_previous_searches']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($searches AS $search) { ?>
                    <a class="list-group-item" target="_blank" href="<?php echo $this->escape($search['url']); ?>"><?php echo $this->escape($search['keywords']); ?><span class="pull-right"><?php echo $search['date']; ?></span></a>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
     $('.quick_statistics_link').qtip({
         show: 'click',
         hide: 'click',
         style: {
             classes: 'qtip-bootstrap qtip-shadow',
         },
         position: { at: "bottom middle", my: "top middle", adjust: { x: 5 }},
         content: {
             text: function(event, api) {
                 return $("#quick_statistics_"+$(this).data('id'));
             }
         }
     }).bind('click', function(e) { return false; });
 });
 </script>