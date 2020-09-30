<?php if($categories) { ?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <a class="pull-right" href="<?php echo BASE_URL; ?>/browse_categories.php"><?php echo $lang['more']; ?></a>
        <h3 class="panel-title"><?php echo $lang['block_categories']; ?></h3>
    </div>
    <div class="panel-body">
        <?php foreach($categories as $category) { ?>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <p>
                    <a<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $category['url']; ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a>
                    <?php if($show_indexes) { ?>
                        &nbsp;(<?php echo $category['count_total']; ?>)
                    <?php } ?>
                </p>
            </div>
        <?php } ?>
    </div>
</div>
<?php } ?>