<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_classified']; ?></h2>
<ul class="media-list">
<?php foreach($records AS $record) { ?>
    <li class="media">
        <?php if($record['image_url']) { ?>
            <a class="pull-left" href="<?php echo $record['url']; ?>">
                <img class="media-object thumbnail" src="<?php echo $record['image_url']; ?>" alt="<?php echo $this->escape($record['title']); ?>">
            </a>
        <?php } ?>
        <div class="media-body">
            <h4 class="media-heading"><a href="<?php echo $record['url']; ?>"><?php echo $this->escape($record['title']); ?></a></h4>
            <p><small><?php echo $this->escape($record['date']); ?></small></p>
            <p><?php echo $this->escape($record['description']); ?></p>
            <p>
            <a class="btn btn-default" href="<?php echo $record['url']; ?>"><span class="fa fa-eye"></span> <?php echo $lang['view']; ?></a>
            <?php if(!empty($record['images'])) { ?>
                <a class="btn btn-default" href="<?php echo $record['images_url']; ?>"><span class="fa fa-picture-o"></span> <?php echo $lang['public_classified_images']; ?></a>
            <?php } ?>
            <?php if(!empty($record['buttoncode'])) { ?>
                <a class="btn btn-default btn-success" target="_blank" href="<?php echo $this->escape($record['buttoncode']); ?>"> <span class="fa fa-money"></span> <?php echo $lang['public_classified_buy']; ?></a>
            <?php } ?>
            </p>
        </div>
    </li>
<?php } ?>
</ul>
<div class="text-center">
    <?php echo $page_navigation; ?>
</div>