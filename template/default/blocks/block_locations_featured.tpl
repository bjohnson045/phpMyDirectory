<?php if($locations) { ?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <a class="pull-right" href="<?php echo BASE_URL; ?>/browse_locations.php"><?php echo $lang['more']; ?></a>
        <h3 class="panel-title"><?php echo $lang['block_locations']; ?></h3>
    </div>
    <div class="panel-body">
        <?php foreach($locations as $location) { ?>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <p>
                    <a<?php if($location['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $location['url']; ?>" title="<?php echo $this->escape($location['title']); ?>"><?php echo $this->escape($location['title']); ?></a>
                    <?php if($show_indexes) { ?>
                        &nbsp;(<?php echo $location['count_total']; ?>)
                    <?php } ?>
                </p>
            </div>
        <?php } ?>
    </div>
</div>
<?php } ?>