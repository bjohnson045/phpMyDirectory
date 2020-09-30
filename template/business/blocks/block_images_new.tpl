<?php if(count($results)) { ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_images_new']; ?></h3>
    </div>
    <ul class="list-group">
    <?php foreach($results as $result) { ?>
        <?php if(!empty($result['image_thumb_url'])) { ?>
        <li class="list-group-item">
            <h5 class="list-group-item-heading"><a href="<?php echo $result['url']; ?>"><?php echo $this->escape($result['title']); ?></a></h5>
            <p><a href="<?php echo $result['url']; ?>"><img class="img-thumbnail" alt="<?php echo $this->escape($result['title']); ?>" src="<?php echo $result['image_thumb_url']; ?>"></a></p>
            <span class="tiny"><?php echo $result['date']; ?></span>
        </li>
        <?php } ?>
    <?php } ?>
    </ul>
</div>
<?php } ?>