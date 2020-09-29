<?php if($category_columns) { ?>
<?php echo $this->block('search_alpha_categories'); ?>
<div class="row row-spaced">
    <?php foreach((array) $category_columns as $column) { ?>
    <div class="col-lg-<?php echo floor(12/count($category_columns)); ?> col-md-<?php echo floor(12/count($category_columns)); ?>">
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
                    <h4>
                    <?php if(!empty($category['link'])) { ?>
                        <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['link']); ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a>
                    <?php } else { ?>
                        <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($category['url']); ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a>
                    <?php } ?>
                    <?php if($show_indexes) { ?>
                        &nbsp;(<?php echo $category['count_total']; ?>)
                    <?php } ?>
                    </h4>
                    <?php if($show_category_description) { ?>
                        <div class="hidden-sm hiddn-xs"><?php echo $this->escape($category['description_short']); ?></div>
                    <?php } ?>
                    <?php if(isset($category['children'])) { ?>
                        <p class="hidden-sm hidden-xs"><small>
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
<br class="clear" />
<?php } ?>
<?php if($location_columns) { ?>
    <?php if(!$category_columns) { ?>
        <?php echo $this->block('search_alpha_locations'); ?>
    <?php } ?>
    <div class="row">
        <?php foreach((array) $location_columns as $column) { ?>
        <div class="col-lg-<?php echo floor(12/count($location_columns)); ?> col-md-<?php echo floor(12/count($location_columns)); ?> col-sm-<?php echo floor(12/count($location_columns)); ?>">
            <?php foreach($column as $location) { ?>
                <div class="media">
                    <?php if($location['image']) { ?>
                        <?php if(!empty($location['link'])) { ?>
                            <a class="pull-left"<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['link']); ?>" title="<?php echo $this->escape($location['title']); ?>"><img class="img-rounded media-object" src="<?php echo $location['image']; ?>" alt="<?php echo $this->escape($location['title']); ?>" /></a>
                        <?php } else { ?>
                            <a class="pull-left"<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['url']); ?>" title="<?php echo $this->escape($location['title']); ?>"><img class="img-rounded media-object" src="<?php echo $location['image']; ?>" alt="<?php echo $this->escape($location['title']); ?>" /></a>
                        <?php } ?>
                    <?php } ?>
                    <div class="media-body">
                        <h4>
                        <?php if(!empty($location['link'])) { ?>
                            <a<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['link']); ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?></a>
                        <?php } else { ?>
                            <a<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['url']); ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?></a>
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
                                <a<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($sublocation['link']); ?>" title="<?php echo $this->escape($sublocation['title']); ?>"><?php echo $this->escape($sublocation['title']); ?></a>
                            <?php } else { ?>
                                <a<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($sublocation['url']); ?>" title="<?php echo $this->escape($sublocation['title']); ?>"><?php echo $this->escape($sublocation['title']); ?></a>
                            <?php } ?>
                            <?php if($loc_show_indexes) { ?>
                                &nbsp;(<?php echo $sublocation['count_total']; ?>)
                            <?php } ?>
                            <?php if(count($location['children']) != $key+1) { ?>,<?php } ?>
                        <?php } ?>
                        <?php if($location['more_children']) { ?>
                            <a<?php if(!$location['count_total']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $this->escape($location['url']); ?>" title="<?php echo $lang['public_index_more']; ?>"> <?php echo $lang['public_index_more']; ?></a>
                        <?php } ?>
                    </small></p>
                    <?php } ?>
                    </div>
                <?php } ?>
        </div>
        <?php } ?>
    </div>
<?php } ?>
<?php if($page) { ?>
    <?php echo $page; ?>
<?php } ?>