<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_categories']; ?></h3>
    </div>
    <?php if($categories) { ?>
        <ul class="list-group">
            <?php foreach($categories as $category) { ?>
                <a class="list-group-item"<?php if($category['no_follow']) { ?> rel="noindex,nofollow"<?php } ?> href="<?php echo $category['url']; ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a>
            <?php } ?>
        </ul>
    <?php } ?>
</div>