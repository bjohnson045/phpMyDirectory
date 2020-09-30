<div itemscope itemtype="http://schema.org/Organization">
    <?php if($logo_background_url) { ?>
        <div class="row row-spaced">
            <div class="col-xs-12">
                <h1><span itemprop="name"><?php echo $this->escape($title); ?></span></h1>
                <img src="<?php echo $logo_background_url; ?>" alt="<?php echo $this->escape($title); ?>" title="<?php echo $this->escape($title); ?>" />
            </div>
        </div>
    <?php } ?>
    <div class="row row-spaced">
        <div class="col-md-6 col-sm-12 clearfix">
            <?php if($last_search_url) { ?>
                <a href="<?php echo $last_search_url; ?>" class="btn btn-default btn-sm pull-right" title="<?php echo $lang['public_listing_view_last_search']; ?>"><span class="fa fa-mail-reply"></span> <span class="fa fa-search"></span></a>
            <?php } ?>
            <?php if(!$logo_background_url) { ?>
                <h1><span itemprop="name"><?php echo $this->escape($title); ?></span></h1>
            <?php } ?>
            <?php if($logo_url) { ?>
                <img class="img-thumbnail pull-left" itemprop="logo" src="<?php echo $logo_url; ?>" alt="<?php echo $this->escape($title); ?>" title="<?php echo $this->escape($title); ?>" />
            <?php } else { ?>
                <i class="pull-left fa fa-picture-o fa-5x"></i>
            <?php } ?>
            <?php if(isset($rating)) { ?>
                <div id="listing_rating" class="stars">
                    <?php for($x=1; $x <=5; $x++) { ?>
                        <span title="<?php echo $x;?> <?php echo $lang['public_listing_stars']; ?>" data-rating="<?php echo $x; ?>" class="star <?php if($x <= $rating) { ?>active<?php } else { ?><?php if(($x - 0.51) < $rating) { ?>active-half<?php } else { ?>deactive<?php } ?><?php } ?>"></span>
                    <?php } ?>
                </div>
		<?php if($rating != '0') { ?>
                <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                    <small class="text-muted"><span itemprop="ratingValue"><?php echo $rating; ?></span> <?php echo $lang['public_listing_stars_from']; ?> <span itemprop="ratingCount"><?php echo $votes; ?></span> <?php echo $lang['public_listing_votes']; ?></small>
                </div>
		<?php } ?>
            <?php } ?>
            <div itemprop="description" content="<?php echo $short_description; ?>"><?php echo $description; ?></div>
       </div>
       <div class="col-md-6 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $lang['public_listing_contact_information']; ?></h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <div itemprop="location" itemscope itemtype="http://schema.org/Place">
                            <div itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">
                                <span itemprop="latitude" content="<?php echo $latitude; ?>"></span>
                                <span itemprop="longitude" content="<?php echo $longitude; ?>"></span>
                            </div>
                            <?php echo $address; ?>
                        </div>
                    </li>
                    <?php if($map_url) { ?>
                        <?php echo $map_url; ?>
                    <?php } ?>
                    <?php if($phone) { ?>
                        <?php if($config['statistics_click_view_phone']) { ?>
                        <li class="list-group-item"><strong><?php echo $lang['public_listing_phone']; ?></strong>:
                            <a id="phone_view" href="#"><?php echo $lang['public_listing_phone_view']; ?></a><a id="phone" itemprop="telephone" href="tel:<?php echo $this->escape($phone); ?>" class="hidden"><?php echo $this->escape($phone); ?></a><?php if($call) { ?> <a class="btn btn-xs btn-info" href="" id="call">Call</a><?php } ?>
                        </li>
                        <script type="text/javascript">
                        $(document).ready(function() {
                            $("#phone_view").click(function(e) {
                                e.preventDefault();
                                $.ajax({
                                    data: ({
                                        action: 'statistic',
                                        type: 'listing_phone_view',
                                        type_id: <?php echo $id; ?>
                                    }),
                                    success: function(phone) {
                                        $("#phone_view").hide();
                                        $("#phone").removeClass('hidden');
                                    }
                                });
                            });
                        });
                        </script>
                        <?php } else { ?>
                            <li class="list-group-item"><strong><?php echo $lang['public_listing_phone']; ?></strong>:
                                <span itemprop="telephone"><?php echo $this->escape($phone); ?></span><?php if($call) { ?> <a class="btn btn-xs btn-info" href="" id="call">Call</a><?php } ?>
                            </li>
                        <?php } ?>
                    <?php } ?>
                    <?php if($fax) { ?>
                        <li class="list-group-item"><strong><?php echo $lang['public_listing_fax']; ?></strong>: <span itemprop="faxNumber"><?php echo $this->escape($fax); ?></span></li>
                    <?php } ?>
                    <?php if($skype_url) { ?>
                        <a class="list-group-item" rel="nofollow" href="<?php echo $skype_url; ?>"><i class="fa fa-skype"></i> <?php echo $lang['public_listing_skype']; ?></a>
                    <?php } ?>
                    <?php if($mail_raw AND $config['statistics_click_view_email']) { ?>
                        <li class="list-group-item"><strong><?php echo $lang['public_listing_email']; ?></strong>:
                            <a id="email_view" href="#"><?php echo $lang['public_listing_email_view']; ?></a><a id="email" itemprop="email" href="mailto:<?php echo $this->escape($mail_raw); ?>" class="hidden"><?php echo $this->escape($mail_raw); ?></a> <a class="btn btn-xs btn-info" href="<?php echo $mail; ?>"><?php echo $lang['public_listing_send_message']; ?></a>
                        </li>
                        <script type="text/javascript">
                        $(document).ready(function() {
                            $("#email_view").click(function(e) {
                                e.preventDefault();
                                $.ajax({
                                    data: ({
                                        action: 'statistic',
                                        type: 'listing_email_view',
                                        type_id: <?php echo $id; ?>
                                    }),
                                    success: function(phone) {
                                        $("#email_view").hide();
                                        $("#email").removeClass('hidden');
                                    }
                                });
                            });
                        });
                        </script>
                    <?php } elseif($mail) { ?>
                        <a class="list-group-item" href="<?php echo $mail; ?>"><span class="glyphicon glyphicon-envelope"></span> <?php echo $lang['public_listing_send_message']; ?></a>
                    <?php } ?>
                    <?php if($www_url) { ?>
                        <a class="list-group-item" rel="nofollow" <?php echo $www_class;?> <?php echo $www_javascript; ?> itemprop="url" href="<?php echo $this->escape($www_url); ?>" target="_blank" id="listing_www" content="<?php echo $www; ?>"><i class="fa fa-external-link"></i> <?php echo $lang['public_listing_www']; ?></a>
                    <?php } ?>
                    <?php if($vcard_url) { ?>
                        <a class="list-group-item" rel="nofollow" href="<?php echo $vcard_url; ?>" target="_blank"><span class="glyphicon glyphicon-info-sign"></span> <?php echo $lang['public_listing_vcard']; ?></a>
                    <?php } ?>
                    <?php if($hours == '24') { ?>
                    <li class="list-group-item list-group-item-success">
                        <?php echo $lang['public_listing_hours_24']; ?>
                    </li>
                    <?php } elseif($hours) { ?>
                    <li class="list-group-item">
                        <p><strong><?php echo $lang['public_listing_hours']; ?></strong><?php if($hours_open) { ?> <span class="label label-success pull-right"><?php echo $lang['public_listing_hours_open']; ?></span><?php } ?></p>
                        <?php foreach($hours AS $day) { ?>
                            <div class="row">
                                <div class="col-xs-4 col-sm-3">
                                    <?php echo $day['title']; ?>:
                                </div>
                                <div class="col-xs-8 col-sm-9">
                                    <?php foreach($day['hours'] AS $hour) { ?>
                                        <div itemprop="openingHoursSpecification" itemscope itemtype="http://schema.org/OpeningHoursSpecification">
                                            <link itemprop="dayOfWeek" href="http://purl.org/goodrelations/v1#<?php echo $day['title']; ?>" />
                                            <meta itemprop="opens" content="<?php echo $hour['start_24']; ?>"/><?php echo $hour['start']; ?> -
                                            <meta itemprop="closes" content="<?php echo $hour['end_24']; ?>"/><?php echo $hour['end']; ?><br />
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </li>
                    <?php } ?>
                    <?php if($social_links) { ?>
                        <li class="list-group-item">
                            <?php if($facebook_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_facebook']; ?>" href="<?php echo $facebook_url; ?>"><span class="fa fa-2x fa-facebook-square"></span></a><?php } ?>
                            <?php if($twitter_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_twitter']; ?>" href="<?php echo $twitter_url; ?>"><span class="fa fa-2x fa-twitter-square"></span></a><?php } ?>
                            <?php if($google_page_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_google_page']; ?>" href="<?php echo $google_page_url; ?>"><span class="fa fa-2x fa-google-plus-square"></span></a><?php } ?>
                            <?php if($linkedin_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_linkedin']; ?>" href="<?php echo $linkedin_url; ?>"><span class="fa fa-2x fa-linkedin"></span></a><?php } ?>
                            <?php if($linkedin_company_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_linkedin_company']; ?>" href="<?php echo $linkedin_company_url; ?>"><span class="fa fa-2x fa-linkedin-square"></span></a><?php } ?>
                            <?php if($pinterest_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_pinterest']; ?>" href="<?php echo $pinterest_url; ?>"><span class="fa fa-2x fa-pinterest-square"></span></a><?php } ?>
                            <?php if($youtube_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_youtube']; ?>" href="<?php echo $youtube_url; ?>"><span class="fa fa-2x fa-youtube-square"></span></a><?php } ?>
                            <?php if($foursquare_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_foursquare']; ?>" href="<?php echo $foursquare_url; ?>"><span class="fa fa-2x fa-foursquare"></span></a><?php } ?>
                            <?php if($instagram_url) { ?><a target="_blank" rel="nofollow" title="<?php echo $lang['public_listing_instagram']; ?>" href="<?php echo $instagram_url; ?>"><span class="fa fa-2x fa-instagram"></span></a><?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
       </div>
    </div>
    <div class="row row-spaced">
    <div class="col-sm-12">
        <?php if($share_url) { ?>
            <?php echo $share; ?>
        <?php } ?>
        <div class="btn-row">
            <?php if($locations_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $locations_url; ?>"><span class="fa fa-map-marker"></span> <?php echo $lang['public_listing_locations']; ?><span class="badge"><?php echo $locations_count; ?></span></a>
            <?php } ?>
            <?php if($classifieds_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $classifieds_url; ?>"><i class="fa fa-dropbox"></i> <?php echo $lang['public_listing_classifieds']; ?><span class="badge"><?php echo $classifieds_count; ?></span></a>
            <?php } ?>
            <?php if($images_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $images_url; ?>"><span class="glyphicon glyphicon-picture"></span> <?php echo $lang['public_listing_images']; ?><span class="badge"><?php echo $images_count; ?></span></a>
            <?php } ?>
            <?php if($events_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $events_url; ?>"><i class="fa fa-calendar"></i> <?php echo $lang['public_listing_events']; ?><span class="badge"><?php echo $events_count; ?></span></a>
            <?php } ?>
            <?php if($jobs_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $jobs_url; ?>"><i class="fa fa-building-o"></i> <?php echo $lang['public_listing_jobs']; ?><span class="badge"><?php echo $jobs_count; ?></span></a>
            <?php } ?>
            <?php if($documents_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $documents_url; ?>"><span class="glyphicon glyphicon-file"></span> <?php echo $lang['public_listing_documents']; ?><span class="badge"><?php echo $documents_count; ?></span></a>
            <?php } ?>
            <?php if($reviews_add_url) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $reviews_add_url; ?>"><span class="glyphicon glyphicon-comment"></span> <?php echo $lang['public_listing_reviews_add']; ?></a>
            <?php } ?>
            <?php if($reviews_count) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $reviews_url; ?>"><span class="glyphicon glyphicon-comment"></span> <?php echo $lang['public_listing_reviews']; ?><span class="badge"><?php echo $reviews_count; ?></span></a>
            <?php } ?>
            <?php if($print) { ?>
                <a class="btn btn-sm btn-default" rel="nofollow" href="<?php echo $print; ?>"><span class="glyphicon glyphicon-print"></span> <?php echo $lang['public_listing_print']; ?></a>
            <?php } ?>
            <?php if($email_friend) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $email_friend; ?>"><span class="glyphicon glyphicon-user"></span> <?php echo $lang['public_listing_email_friend']; ?></a>
            <?php } ?>
            <?php if($send_to_phone) { ?>
                <a class="btn btn-sm btn-default" id="send_to_phone" href=""><span class="glyphicon glyphicon-phone"></span> <?php echo $lang['public_listing_send_to_phone']; ?></a>
            <?php } ?>
            <?php if($pdf_url) { ?>
                <a class="btn btn-sm btn-default" rel="nofollow" href="<?php echo $pdf_url; ?>" target="_blank"><span class="glyphicon glyphicon-file"></span> <?php echo $lang['public_listing_pdf_download']; ?></a>
            <?php } ?>
            <?php if($favorites_url) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $favorites_url; ?>"><span class="glyphicon glyphicon-heart<?php if($favorites) { ?> text-danger<?php } ?>"></span> <?php echo $favorites_text; ?></a>
            <?php } ?>
            <?php if($suggestion_url) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $suggestion_url; ?>"><span class="glyphicon glyphicon-question-sign"></span> <?php echo $lang['public_listing_suggestion']; ?></a>
            <?php } ?>
            <?php if($claim_url) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $claim_url; ?>"><span class="glyphicon glyphicon-check"></span> <?php echo $lang['public_listing_claim']; ?></a>
            <?php } ?>
            <?php if($report_url) { ?>
                <a class="btn btn-sm btn-default" href="<?php echo $report_url; ?>"><span class="glyphicon glyphicon-exclamation-sign"></span> <?php echo $lang['public_listing_report']; ?></a>
            <?php } ?>
        </div>
    </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <?php if($map) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $lang['public_listing_map']; ?></h3>
                    </div>
                    <?php echo $map; ?>
                </div>
            <?php } ?>
            <?php if($locations_count) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php if($locations_count > 1) { ?>
                            <a href="<?php echo $locations_url; ?>" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                        <?php } ?>
                        <h3 class="panel-title"><?php echo $lang['public_listing_locations']; ?></h3>
                    </div>
                    <?php foreach($listings_locations AS $listings_location) { ?>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <h4 class="list-group-item-heading">
                                <?php echo $this->escape($listings_location['title']); ?>
                            </h4>
                            <p class="list-group-item-text">
                                <?php echo $this->escape($listings_location['formatted']); ?>
                                <?php if(!empty($listings_location['phone'])) { ?>
                                    <br><a href="tel:<?php echo $this->escape($listings_location['phone']); ?>"><?php echo $this->escape($listings_location['phone']); ?></a>
                                <?php } ?>
                                <?php if(!empty($listings_location['url'])) { ?>
                                    <br><a target="_blank" rel="nofollow" href="<?php echo $this->escape($listings_location['url']); ?>"><?php echo $this->escape($listings_location['url']); ?></a>
                                <?php } ?>
                                <?php if(!empty($listings_location['email'])) { ?>
                                    <br><a href="mailto:<?php echo $this->escape($listings_location['email']); ?>"><?php echo $this->escape($listings_location['email']); ?></a>
                                <?php } ?>
                                <br><a target="_blank" rel="nofollow" href="<?php echo $this->escape($listings_location['map_url']); ?>"><?php echo $lang['public_listing_map']; ?></a>
                            </p>
                        </li>
                    </ul>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <div class="col-md-4 col-sm-12">
            <?php echo $custom_fields; ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $lang['public_listing_other_information']; ?></h3>
                </div>
                <div class="panel-body">
                    <strong><?php echo $lang['public_listing_categories']; ?>:</strong>
                    <?php foreach($categories AS $category) { ?>
                        <p><?php echo $category['path_url']; ?></p>
                    <?php } ?>
                    <?php if($related_listings) { ?>
                        <p>
                            <strong><?php echo $lang['public_listing_related_listings']; ?>:</strong><br />
                            <?php foreach($related_listings AS $related_listing) { ?>
                                <a href="<?php echo $related_listing['url']; ?>"><?php echo $related_listing['title']; ?></a><br />
                            <?php } ?>
                        </p>
                    <?php } ?>
                    <?php if($qrcode) { ?>
                        <p><?php echo $qrcode; ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php if($images) { ?>
    <div class="row">
        <div class="col-sm-12">
            <div>
                <span class="pull-right"><small><a href="<?php echo $images_url; ?>"><?php echo $lang['view_all']; ?></a></small></span>
                <h2><?php echo $lang['public_listing_images']; ?></h2>
            </div>
            <div class="row">
                <?php foreach($images as $key=>$image) { ?>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <a class="image_group thumbnail" rel="image_group" href="<?php echo $image['image']; ?>" title="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                        <img src="<?php echo $image['thumb']; ?>" alt="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
    <?php if($reviews) { ?>
    <div class="row row-spaced">
        <div class="col-sm-12">
            <div>
                <span class="pull-right"><small><a href="<?php echo $reviews_url; ?>"><?php echo $lang['view_all']; ?></a></small></span>
                <h2><?php echo $lang['public_listing_reviews']; ?></h2>
            </div>
            <?php foreach($reviews AS $review) { ?>
                <?php echo $review; ?>
            <?php } ?>
        </div>
   </div>
   <?php } ?>
    <?php if($events) { ?>
    <div class="row">
        <div class="col-sm-12">
            <div>
                <span class="pull-right"><small><a href="<?php echo $events_url; ?>"><?php echo $lang['view_all']; ?></a></small></span>
                <h2><?php echo $lang['public_listing_events']; ?></h2>
            </div>
            <?php foreach($events as $key=>$event) { ?>
                <?php echo $event['date_start']; ?> - <a href="<?php echo $this->escape($event['url']); ?>"><?php echo $this->escape($event['title']); ?></a><br />
            <?php } ?>
        </div>
    </div>
    <?php } ?>
    <?php if($blog_posts) { ?>
    <div class="row">
        <div class="col-sm-12">
            <div>
                <span class="pull-right"><small><a href="<?php echo $blog_posts_url; ?>"><?php echo $lang['view_all']; ?></a></small></span>
                <h2><?php echo $lang['public_listing_blog_posts']; ?></h2>
            </div>
            <?php foreach($blog_posts as $key=>$blog_post) { ?>
                <?php echo $blog_post['date']; ?> - <a href="<?php echo $this->escape($blog_post['url']); ?>"><?php echo $this->escape($blog_post['title']); ?></a><br />
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
<?php if($rating_allowed) { ?>
    <script type="text/javascript">
    $(document).ready(function(){
        // Reset the stars based on the hidden input value
        var stars_reset = function() {
            // Cycle through each star
            $('#listing_rating.stars .star').each(function() {
                // Remove the hover class and reapply the standard class
                $(this).removeClass('hover');
                // If the star is less than or equal to the current value, fill in the star
                if(parseInt($('#listing_rating_value').val()) >= parseInt($(this).data('rating'))) {
                    return $(this).removeClass('deactive').addClass('active');
                } else {
                    return $(this).removeClass('active').addClass('deactive');
                }
            });
        }
        $('#listing_rating.stars .star').on({
            // When hovering over each star
            mouseenter: function() {
                // Fill in the star and apply the hover class to the star and any stars before it
                $(this).prevAll().andSelf().removeClass('deactive active').addClass('hover');
                // For each star after the one being hovered on
                $(this).nextAll().each(function() {
                    // Remove the hover class and reapply the standard class
                    $(this).removeClass('hover').addClass('active');
                    // If the star is greater than the current value empty the star
                    if(parseInt($(this).data('rating')) > parseInt($('#listing_rating_value').val())) {
                        $(this).removeClass('active').addClass('deactive');
                    }
                });
            },
            // Set the value when a star is clicked, and reset the stars based on the new value
            click: function() {
                $.ajax({
                    data: ({
                        action: 'save_rating',
                        rating: $(this).data('rating'),
                        listing_id: <?php echo $id; ?>
                    }),
                    success: function() {
                        window.location.href = '<?php echo $this->escape(URL); ?>';
                    }
                });
                return stars_reset();
            }
        });

        // When hovering completely out of the stars element, reset the stars based on the current value
        $('#listing_rating.stars').hover(function(){},function(){
            stars_reset();
        });
        // Initially reset the stars based on the current value
        stars_reset();
    });
    </script>
    <input type="hidden" id="listing_rating_value" value="<?php echo $this->escape($rating); ?>">
<?php } ?>
<div id="send_to_phone_container" style="display: none;">
    <div id="send_to_phone_result" style="display: none;"></div>
    <div id="send_to_phone_form">
        <p><?php echo $lang['public_listing_send_to_phone_message']; ?></p>
        <p><?php echo $lang['public_listing_send_to_phone_number']; ?>: <input type="text" class="form-control" id="send_to_phone_number" placeholder="xxx-xxx-xxxx"></p>
        <p class="text-muted"><?php echo $lang['public_listing_send_to_phone_notes']; ?></p>
        <button class="btn btn-primary" id="send_to_phone_send"><?php echo $lang['public_listing_send_to_phone_send']; ?></button>
    </div>
</div>
<div id="call_container" style="display: none;">
    <div id="call_result" style="display: none;"></div>
    <div id="call_form">
        <p><?php echo $lang['public_listing_call_message']; ?></p>
        <p><?php echo $lang['public_listing_send_to_phone_number']; ?>: <input type="text" class="form-control" id="call_number" placeholder="xxx-xxx-xxxx"></p>
        <p class="text-muted"><?php echo $lang['public_listing_send_to_phone_notes']; ?></p>
        <button class="btn btn-primary" id="call_send"><?php echo $lang['public_listing_call']; ?></button>
    </div>
</div>