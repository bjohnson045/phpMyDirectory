<div class="controlset">
    <textarea<?php echo $attributes; ?>><?php echo $value; ?></textarea>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('#<?php echo $name; ?>').ckeditor(function(){
        this.dataProcessor.htmlFilter.addRules({
            elements: {
                img: function( el ) {
                    el.addClass('img-responsive');
                }
            }
        });
    }, {
        scayt_autoStartup: false,
        disableNativeSpellChecker: true,
        htmlEncodeOutput: false,
        <?php if(LOGGED_IN) { ?>
            filebrowserWindowWidth: 800,
            filebrowserWindowHeight: 500,
            allowedContent: '<?php echo $allowed_tags; ?>',
            <?php if($listing_id) { ?>
                <?php if($browse) { ?>
                    filebrowserBrowseUrl: '<?php echo BASE_URL; ?>/includes/ckeditor/browser/browse.php?listing_id=<?php echo $listing_id; ?>',
                <?php } ?>
                toolbar: 'upload'
            <?php } else { ?>
                toolbar: 'basic'
            <?php } ?>
        <?php } ?>
        <?php if($lang['textdirection'] == 'RTL') { ?>
            , contentsLangDirection: 'rtl'
        <?php } ?>
        , language: '<?php echo substr($lang['languagecode'],0,2); ?>'
        <?php if($fullpage) { ?>
            , fullPage: true
        <?php } ?>
        <?php if(isset($counter)) { ?>
            ,extraPlugins: 'charcount',
            charcount_limit: <?php echo $counter; ?>
        <?php } ?>
        <?php if(isset($options)) { ?>
            <?php foreach((array) $element['options'] AS $option=>$value) { ?>
                , $option: '<?php echo $value; ?>'
            <?php } ?>
        <?php } ?>
    });
});
</script>