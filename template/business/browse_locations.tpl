<?php if(!empty($location_image)) { ?>
    <p><img src="<?php echo $location_image; ?>" alt="<?php echo $location_title; ?>" /></p>
<?php } ?>
<?php if(isset($location_title)) { ?>
    <h3><?php echo $location_title; ?><?php if(isset($category_title)) { ?> <?php echo $this->escape($category_title); ?><?php } ?></h3>
    <div class="row">
        <div class="col-lg-9 col-md-6 col-sm-6 col-xs-12">
            <?php if(count($breadcrumb)) { ?>
                <p>
                    <?php echo $lang['public_browse_locations']; ?>
                    <?php foreach($breadcrumb as $key=>$crumb) { ?>
                        <a href="<?php echo $crumb['link']; ?>" title="<?php echo $this->escape($crumb['text']); ?>"><?php echo $this->escape($crumb['text']); ?></a><?php if($key+1 != count($breadcrumb)) { ?> &raquo;<?php } ?>
                    <?php } ?>
                </p>
            <?php } ?>
        </div>
        <?php if($add_listing) { ?>
        <div class="col-lg-3 col-md-6 col-sm-6 hidden-xs">
            <p class="pull-right"><a rel="noindex, nofollow" class="btn btn-default btn-xs" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders_add_listing.php?location_id=<?php echo $location_id; ?>"><?php echo $lang['public_browse_locations_add_listing']; ?></a></p>
        </div>
        <?php } ?>
    </div>
    <?php if(!empty($location_description)) { ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo $location_description; ?>
            </div>
        </div>
    <?php } elseif(!empty($location_description_short)) { ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo $location_description_short; ?>
            </div>
        </div>
    <?php } ?>
    <?php echo $custom_fields; ?>
<?php } else { ?>
    <?php echo $this->block('search_alpha_locations'); ?>
<?php } ?>
<?php if($category_columns) { ?>
<div class="row visible-xs">
    <div class="col-lg-12">
        <select id="category_select" class="form-control">
        <option><?php echo $lang['public_browse_locations_filter_by_category']; ?></option>
        <?php foreach((array) $category_columns as $column) { ?>
            <?php foreach($column as $category) { ?>
                <option value="<?php echo $this->escape($category['url']); ?>"><?php echo $category['title']; ?></option>
            <?php } ?>
        <?php } ?>
        </select>
        <script type="text/javascript">
        $(document).ready(function(){
            $('#category_select').on('change',function() {
                var url = $(this).val();
                if (url) {
                    window.location = url; // redirect
                }
                return false;
            });
        });
        </script>
    </div>
</div>
<div class="row hidden-xs">
    <?php foreach((array) $category_columns as $column) { ?>
    <div class="col-spaced col-lg-<?php echo floor(12/count($category_columns)); ?> col-md-<?php echo floor(12/count($category_columns)); ?> col-sm-<?php echo floor(12/count($category_columns)); ?>">
        <?php foreach($column as $category) { ?>
            <div class="media">
                <?php if($category['image']) { ?>
                    <?php if(!empty($category['link'])) { ?>
                        <a class="pull-left"<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['link']); ?>" title="<?php echo $this->escape($category['title']); ?>"><img class="img-rounded media-object" src="<?php echo $category['image']; ?>" alt="<?php echo $this->escape($category['title']); ?>" /></a>
                    <?php } else { ?>
                        <a class="pull-left"<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['url']); ?>" title="<?php echo $this->escape($category['title']); ?>"><img class="img-rounded media-object" src="<?php echo $category['image']; ?>" alt="<?php echo $this->escape($category['title']); ?>" /></a>
                    <?php } ?>
                <?php } ?>
                <div class="media-body">
                    <h4 class="media-heading">
                    <?php if(!empty($category['link'])) { ?>
                        <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['link']); ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a>
                    <?php } else { ?>
                        <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['url']); ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a>
                    <?php } ?>
                    </h4>
                    <?php if($show_category_description) { ?>
                        <?php echo $this->escape($category['description_short']); ?>
                    <?php } ?>
                    <?php if(isset($category['children'])) { ?>
                        <p class="hidden-xs"><small>
                            <?php foreach($category['children'] as $key=>$subcategory) { ?>
                                <?php if(!empty($subcategory['link'])) { ?>
                                    <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($subcategory['link']); ?>" title="<?php echo $this->escape($subcategory['title']); ?>"><?php echo $this->escape($subcategory['title']); ?></a>
                                <?php } else { ?>
                                    <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($subcategory['url']); ?>" title="<?php echo $this->escape($subcategory['title']); ?>"><?php echo $this->escape($subcategory['title']); ?></a>
                                <?php } ?>
                                <?php if($show_indexes) { ?>
                                    &nbsp;(<?php echo $subcategory['count_total']; ?>)
                                <?php } ?>
                                <?php if(count($category['children']) != $key+1) { ?>,<?php } ?>
                            <?php } ?>
                            <?php if($category['more_children']) { ?>
                                <a<?php if(!$category['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['url']); ?>" title="<?php echo $lang['public_index_more']; ?>"> <?php echo $lang['public_index_more']; ?></a>
                            <?php } ?>
                        </small></p>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>
<?php } ?>
<?php if($location_columns) { ?>
<div class="row">
    <?php foreach((array) $location_columns as $column) { ?>
    <div class="col-spaced col-lg-<?php echo floor(12/count($location_columns)); ?> col-md-<?php echo floor(12/count($location_columns)); ?> col-sm-<?php echo floor(12/count($location_columns)); ?>">
        <?php foreach($column as $location) { ?>
            <div class="media">
                <?php if($location['image']) { ?>
                    <?php if(!empty($location['link'])) { ?>
                        <a class="pull-left"<?php if($location['no_follow'] OR !$location['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['link']); ?>" title="<?php echo $this->escape($location['title']); ?>"><img class="img-rounded media-object" src="<?php echo $location['image']; ?>" alt="<?php echo $this->escape($location['title']); ?>" /></a>
                    <?php } else { ?>
                        <a class="pull-left"<?php if($location['no_follow'] OR !$location['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['url']); ?>" title="<?php echo $this->escape($location['title']); ?>"><img class="img-rounded media-object" src="<?php echo $location['image']; ?>" alt="<?php echo $this->escape($location['title']); ?>" /></a>
                    <?php } ?>
                <?php } ?>
                <div class="media-body">
                    <h4 class="media-heading">
                    <?php if(!empty($location['link'])) { ?>
                        <a<?php if($location['no_follow'] OR !$location['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['link']); ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?></a>
                    <?php } else { ?>
                        <a<?php if($location['no_follow'] OR !$location['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['url']); ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?></a>
                    <?php } ?>
                    <?php if($loc_show_indexes) { ?>
                        &nbsp;(<?php echo $location['count_total']; ?>)
                    <?php } ?>
                    </h4>
                </div>
                <?php if($show_location_description) { ?>
                    <?php echo $this->escape($location['description_short']); ?>
                <?php } ?>
                <?php if(isset($location['children'])) { ?>
                <p class="hidden-xs"><small>
                    <?php foreach((array) $location['children'] as $key=>$sublocation) { ?>
                        <?php if(!empty($sublocation['link'])) { ?>
                            <a<?php if($location['no_follow'] OR $sublocation['no_follow'] OR !$sublocation['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($sublocation['link']); ?>" title="<?php echo $this->escape($sublocation['title']); ?>"><?php echo $this->escape($sublocation['title']); ?></a>
                        <?php } else { ?>
                            <a<?php if($location['no_follow'] OR $sublocation['no_follow'] OR !$sublocation['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($sublocation['url']); ?>" title="<?php echo $this->escape($sublocation['title']); ?>"><?php echo $this->escape($sublocation['title']); ?></a>
                        <?php } ?>
                        <?php if($loc_show_indexes) { ?>
                            &nbsp;(<?php echo $sublocation['count_total']; ?>)
                        <?php } ?>
                        <?php if(count($location['children']) != $key+1) { ?>,<?php } ?>
                    <?php } ?>
                    <?php if($location['more_children']) { ?>
                        <a<?php if($location['no_follow'] OR !$location['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['url']); ?>" title="<?php echo $lang['public_index_more']; ?>"> <?php echo $lang['public_index_more']; ?></a>
                    <?php } ?>
                </small></p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>
<?php } ?>
<br class="clear" />
<?php if($results_amount > 0) { ?>
    <?php if($results_amount > 1) { ?>
        <div style="margin-bottom: 15px;">
            <?php echo $lang['public_browse_locations_search_within']; ?>:
            <?php echo $form_search_within->getFormOpenHTML(array('class'=>'form-inline')); ?>
            <?php echo $form_search_within->getFieldHTML('keyword'); ?><?php echo $form_search_within->getFieldHTML('submit'); ?>
            <?php echo $form_search_within->getFieldHTML('location_id'); ?>
            <?php echo $form_search_within->getFormCloseHTML(); ?>
        </div>
    <?php } ?>
     <h2><?php echo $lang['public_general_table_list_results']; ?></h2>
    <?php echo $listing_results; ?>
<?php } elseif($_GET['id'] != 1 AND !$category_columns AND !$location_columns) { ?>
    <p><?php echo $lang['public_browse_locations_no_results']; ?></p>
<?php } ?>