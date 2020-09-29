<?php if($document_count) { ?>
    <p><strong><?php echo $lang['public_search_documents_results']; ?>:</strong> <?php echo $document_count; ?></p>
    <?php foreach($document_results as $document) { ?>
        <div class="list-group">
            <div class="list-group-item">
                <a class="btn btn-default pull-right" href="<?php echo $document['document_url']; ?>"><?php echo $lang['public_search_documents_download']; ?></a>
                <h4 class="list-group-item-heading">
                    <a href="<?php echo $this->escape($document['url']); ?>"><?php echo $document['title']; ?></a>
                </h4>
                <p><small>from <a href="<?php echo $this->escape($document['url']); ?>"><?php echo $this->escape($document['listing_title']); ?></a></small></p>
                <p class="list-group-item-text">
                    <?php echo $this->escape($document['description']); ?>
                </p>
            </div>
        </div>
    <?php } ?>
    <div class="text-center">
        <?php echo $page_navigation; ?>
    </div>
<?php } else { ?>
    <?php echo $lang['public_search_documents_no_results']; ?>
<?php } ?>