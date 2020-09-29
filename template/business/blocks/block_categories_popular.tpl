<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_categories_popular']; ?><a class="pull-right" href="<?php echo BASE_URL; ?>/xml.php?type=rss_popular_categories"><i class="fa fa-rss"></i></a></h3>
    </div>
    <ul class="list-group">
        <?php foreach($categories as $category) { ?>
            <li class="list-group-item"><a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $category['url']; ?>"><?php echo $this->escape($category['title']); ?></a> <small>(<?php echo $category['impressions']; ?> <?php echo $lang['block_category_impressions']; ?>)</small></li>
        <?php } ?>
    </ul>
</div>
