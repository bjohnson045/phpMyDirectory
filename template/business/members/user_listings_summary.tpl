<?php if($reciprocal_retry) { ?>
    <?php echo $reciprocal_retry; ?>
<?php } ?>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_listings.php?action=edit&id=<?php echo $listing['id']; ?>&user_id=<?php echo $listing['user_id']; ?>" class="btn btn-default btn-xs pull-right"><?php echo $lang['user_edit']; ?></a>
                <h3 class="panel-title"><?php echo $lang['user_listings_details']; ?></h3>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_id']; ?>:</td><td><?php echo $listing['id']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_public_url']; ?>:</td>
                    <td>
                        <p><a target=_blank" href="<?php echo $listing['url']; ?>"><?php echo $listing['url_short']; ?></a></p>
                        <?php echo $share; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_primary_category']; ?>:</td><td><?php echo $primary_category; ?></td>
                </tr>
                <?php if(!empty($listing['address'])) { ?>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_location']; ?>:</td><td><?php echo $this->escape($listing['address']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_date_submitted']; ?>:</td><td><?php echo $listing['date']; ?></td>
                </tr>
                <?php if(!empty($listing['date_update'])) { ?>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_date_updated']; ?>:</td><td><?php echo $listing['date_update']; ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders_view.php?action=edit&id=<?php echo $order['id']; ?>&user_id=<?php echo $listing['user_id']; ?>" class="btn btn-default btn-xs pull-right"><?php echo $lang['view']; ?></a>
                <h3 class="panel-title"><?php echo $lang['user_orders_details']; ?></h3>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_order_number']; ?>:</td><td><?php echo $order['order_id']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_type']; ?>:</td>
                    <td>
                        <?php echo $this->escape($order['product_group_name']); ?> - <?php echo $this->escape($order['product_name']); ?>
                        <?php if($order['upgrades_link'] == true) { ?>
                            <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders_change.php?id=<?php echo $order['id'] ?>" class="btn btn-default btn-xs btn-info pull-right"><?php echo $lang['user_listings_change_plan']; ?></a>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_next_due_date']; ?>:</td>
                    <td>
                        <?php echo $order['next_due_date']; ?>
                        <?php if($order['renew']) { ?>
                            <a class="btn btn-default btn-success btn-xs pull-right" href="user_orders.php?action=renew&id=<?php echo $order['id']; ?>"><?php echo $lang['user_orders_renew']; ?></a>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_listings_status']; ?>:</td><td><?php echo $lang[$listing['status']]; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $lang['user_listings_usage_limits']; ?></h3>
            </div>
            <ul class="list-group">
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array((count($listing['categories'])+1),$listing['category_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_categories']; ?>
                </li>
                <?php if($listing['locations_limit']) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array($locations_used,$listing['locations_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_locations']; ?>
                </li>
                <?php } ?>
                <?php if($listing['documents_limit']) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array($documents_used,$listing['documents_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_documents']; ?>
                </li>
                <?php } ?>
                <?php if($listing['classifieds_limit']) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array($classifieds_used,$listing['classifieds_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_classifieds']; ?>
                </li>
                <?php } ?>
                <?php if($listing['images_limit']) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array($images_used,$listing['images_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_images']; ?>
                </li>
                <?php } ?>
                <?php if($listing['events_limit']) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array($events_used,$listing['events_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_events']; ?>
                </li>
                <?php } ?>
                <?php if($config['blog_user_posts'] AND $listing['blog_posts_limit'] AND ADDON_BLOG) { ?>
                <li class="list-group-item">
                    <div class="pull-right">
                        <?php echo $this->getLanguage('user_listings_usage_limit',array($blog_posts_used,$listing['blog_posts_limit'])); ?>
                    </div>
                    <?php echo $lang['user_listings_blog_posts']; ?>
                </li>
                <?php } ?>
                <?php if($banner_types) { ?>
                    <?php foreach($banner_types AS $banner_type) { ?>
                        <?php if($listing['banner_limit_'.$banner_type['id']] > 0) { ?>
                            <li class="list-group-item">
                                <div class="pull-right">
                                    <?php echo $this->getLanguage('user_listings_usage_limit',array(${'banners_used_'.$banner_type['id']},$listing['banner_limit_'.$banner_type['id']])); ?>
                                </div>
                                <?php echo $this->escape($banner_type['name']); ?>
                            </li>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_listings_statistics.php?listing_id=<?php echo $listing['id']; ?>" class="btn btn-default btn-xs pull-right"><?php echo $lang['user_listings_view_full_statistics']; ?></a>
                <h3 class="panel-title"><span class="fa fa-bar-chart-o"></span> <?php echo $lang['user_listings_quick_statistics']; ?></h3>
            </div>
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
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <?php if($reviews) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_listings_reviews.php?listing_id=<?php echo $listing['id']; ?>" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                <h3 class="panel-title"><span class="fa fa-star"></span> <?php echo $lang['user_listings_recent_reviews']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($reviews AS $review) { ?>
                <li class="list-group-item">
                    <div class="pull-right"><?php echo $review['rating_static']; ?></div>
                    <a target="_blank" href="<?php echo BASE_URL; ?>/listing_reviews.php?id=<?php echo $listing['id']; ?>"><?php echo $this->escape($review['title']); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>