<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_blog_posts']; ?><a class="pull-right" href="<?php echo BASE_URL; ?>/xml.php?type=rss_blog"><i class="fa fa-rss"></i></a></h3>
    </div>
    <?php if(count($blog_posts)) { ?>
        <ul class="list-group">
        <?php foreach($blog_posts AS $blog_post) { ?>
            <li class="list-group-item">
                <h5 class="list-group-item-heading">
                    <a href="<?php echo $blog_post['url']; ?>"><?php echo $this->escape($blog_post['title']); ?></a>
                </h5>
                <p class="list-group-item-text"><?php echo $this->escape_html($blog_post['content']); ?></p>
            </li>
        <?php } ?>
        </ul>
    <?php } else { ?>
         <?php echo $lang['block_blog_none']; ?>
    <?php } ?>
</div>