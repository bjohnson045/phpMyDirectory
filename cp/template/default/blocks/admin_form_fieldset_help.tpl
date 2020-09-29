 <a tabindex="0" role="button" id="<?php echo $name; ?>_help" data-trigger="focus">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
 </a>
 <div id="<?php echo $name; ?>_help_content" class="hidden">
    <?php echo $help; ?>
 </div>
 <?php if(isset($title) AND $title!='') { ?>
    <div id="<?php echo $name; ?>_help_title" class="hidden">
        <?php echo $title; ?>
    </div>
 <?php } ?>
 <script type="text/javascript">
 $(document).ready(function() {
    $("#<?php echo $name; ?>_help").popover({
        html: true,
        content: function() {
            return $("#<?php echo $name; ?>_help_content").html();
        }
        <?php if(isset($title) AND $title!='') { ?>
        ,
        title: function() {
            return $("#<?php echo $name; ?>_help_title").html();
        }
        <?php } ?>
    });
});
</script>