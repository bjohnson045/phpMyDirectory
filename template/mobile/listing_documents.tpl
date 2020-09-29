<h3 style="margin: 0 0 25px 0;"><?php echo $this->escape($listing['title']); ?></h3>
<ul data-role="listview" style="margin-bottom: 5px;">
    <li data-role="list-divider" role="heading"><?php echo $lang['public_listing_documents']; ?></li>
    <?php foreach($documents AS $document) { ?>
        <li>
            <a data-ajax="false" href="<?php echo $document['download_url']; ?>">
                <h3><?php echo $this->escape($document['title']); ?></h3>
                <p><?php echo $this->escape($document['description']); ?></p>
                <p><?php echo $this->escape($document['date']); ?></p>
            </a>
        </li>
    <?php } ?>
</ul>