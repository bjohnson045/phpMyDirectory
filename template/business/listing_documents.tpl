<p><a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a></p>
<?php foreach($records AS $record) { ?>
<div class="list-group">
    <div class="list-group-item">
        <a class="btn btn-default pull-right" href="<?php echo $record['download_url']; ?>"><span class="fa fa-download"></span> <?php echo $lang['public_listing_documents_download']; ?></a>
        <h2><?php echo $this->escape($record['title']); ?></h2>
        <small><?php echo $this->escape($record['date']); ?></small>
        <p><?php echo $this->escape($record['description']); ?></p>
        <?php echo $record['share']; ?>
    </div>
</div>
<?php } ?>