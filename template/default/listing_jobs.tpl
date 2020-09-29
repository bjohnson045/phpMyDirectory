<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_jobs']; ?></h2>
<ul class="media-list">
<?php foreach($records AS $record) { ?>
    <h4><a href="<?php echo $record['url']; ?>"><?php echo $this->escape($record['title']); ?></a></h4>
    <p><small><?php echo $this->escape($record['date']); ?></small></p>
    <p><?php echo $this->escape($record['description_short']); ?></p>
<?php } ?>
</ul>
<div class="text-center">
    <?php echo $page_navigation; ?>
</div>