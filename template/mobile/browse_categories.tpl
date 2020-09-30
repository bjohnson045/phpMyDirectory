<?php if($location_columns) { ?>
    <ul data-role="listview" data-filter="true" style="margin-bottom: 15px;" data-filter-placeholder="Filter Locations">
        <li data-role="list-divider" role="heading">
            Locations
        </li>
        <?php foreach((array) $location_columns as $column) { ?>
            <?php foreach($column as $location) { ?>
                <?php if(!empty($location['link'])) { ?>
                    <li><a href="<?php echo $location['link']; ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?></a></li>
                <?php } else { ?>
                    <li><a href="<?php echo $location['url']; ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?><span class="ui-li-count"><?php echo $location['count_total']; ?></span></a></li>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </ul>
<?php } ?>
<?php if($category_columns) { ?>
    <ul data-role="listview" data-filter="true" style="margin-bottom: 15px;" data-filter-placeholder="Filter Categories">
        <li data-role="list-divider" role="heading">
            Categories
        </li>
        <?php foreach((array) $category_columns as $column) { ?>
            <?php foreach($column as $category) { ?>
                <?php if(!empty($category['link'])) { ?>
                    <li><a href="<?php echo $category['link']; ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a></li>
                <?php } else { ?>
                    <li><a href="<?php echo $category['url']; ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?><span class="ui-li-count"><?php echo $category['count_total']; ?></span></a></li>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </ul>
<?php } ?>
<?php if($results_amount > 0) { ?>
    <?php echo $listing_results; ?>
<?php } elseif($_GET['id'] != 1 AND !$category_columns AND !$location_columns) { ?>
    <?php echo $lang['public_browse_categories_no_results']; ?>
<?php } ?>