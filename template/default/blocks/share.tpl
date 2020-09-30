<?php if($default) { ?>
    <?php if($compact) { ?>
        <div class="addthis_toolbox addthis_default_style addthis_32x32_style"<?php if($url) { ?> addthis:url="<?php echo $url; ?>"<?php } ?><?php if($title) { ?> addthis:title="<?php echo $this->escape($title); ?>"<?php } ?>>
            <a class="addthis_button_preferred_1"></a>
            <a class="addthis_button_preferred_2"></a>
            <a class="addthis_button_preferred_3"></a>
            <a class="addthis_button_preferred_4"></a>
            <a class="addthis_button_compact"></a>
        </div>
    <?php } else { ?>
        <div class="addthis_toolbox addthis_default_style"<?php if($url) { ?> addthis:url="<?php echo $url; ?>"<?php } ?><?php if($title) { ?> addthis:title="<?php echo $this->escape($title); ?>"<?php } ?>>
            <a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
            <a class="addthis_button_tweet"></a>
            <!-- Forcefully reduce the width of the G+1 button -->
            <div style="width: 35px; overflow: hidden; display: inline-block;">
                <a class="addthis_button_google_plusone" g:plusone:size="medium" g:plusone:annotation="none"></a>
            </div>
            <a class="addthis_counter addthis_pill_style"></a>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="addthis_sharing_toolbox"<?php if($url) { ?> data-url="<?php echo $this->url; ?>"<?php } ?><?php if($title) { ?> data-title="<?php echo $this->escape($title); ?>"<?php } ?><?php if($image) { ?> data-media="<?php echo $image; ?>"<?php } ?>></div>
<?php } ?>
<?php if($share_event_action) { ?>
    <script type="text/javascript">
    addthis.addEventListener('addthis.menu.share', function(evt) {
        $.ajax({
            data: ({
                action: '<?php echo $share_event_action; ?>',
                type: '<?php echo $share_event_type; ?>',
                type_id: <?php echo $share_event_type_id; ?>
            }),
            success: function() {}
        });
    });
    </script>
<?php } ?>