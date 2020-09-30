<div class="map_search_popup">
    <?php if($logo_thumb_url) { ?>
        <img class="img-thumbnail pull-left hidden-xs" src="<?php echo $logo_thumb_url; ?>" />
    <?php } ?>
    <p><a href="<?php echo $url; ?>"><?php echo $this->escape($title); ?></a></p>
    <?php echo $this->escape($address); ?>
</div>