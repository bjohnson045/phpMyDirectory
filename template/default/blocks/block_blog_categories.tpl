<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_blog_categories']; ?></h3>
    </div>
    <ul class="list-group">
        <?php foreach($blog_categories as $category) { ?>
            <li class="list-group-item"><a href="<?php echo $category['url']; ?>" title="<?php echo $this->escape($category['title']); ?>"><?php echo $this->escape($category['title']); ?></a><span class="badge"><?php echo $category['post_count']; ?></span></li>
        <?php } ?>
    </ul>
</div>
