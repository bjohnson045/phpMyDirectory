<?php foreach($categories AS $category) { ?>
    <a href="<?php echo $category['url']; ?>" title="<?php echo $category['title']; ?>"><?php echo $category['title']; ?></a> (<?php echo $category['post_count']; ?>)<br />
<?php } ?>