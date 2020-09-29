<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_events']; ?></h2>
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
            <p><small><?php echo $this->escape($record['date_start']); ?></small></p>
            <p><?php echo $this->escape($record['description_short']); ?></p>
        </div>
    </li>
<?php } ?>
</ul>
<div class="text-center">
    <?php echo $page_navigation; ?>
</div>