<script type="text/javascript">
$(document).ready(function(){
    $('div[id^="trace_link"]').dialog({
         buttons: {
            "Close": function() { $(this).dialog("close"); }
         },
         width: 800,
         height: 450,
         autoOpen: false,
         modal: true,
         resizable: false,
         title: "<?php echo $lang['admin_error_log_trace']; ?>"
    });
    $('a[id^="trace_link"]').click(function(e) {
        e.preventDefault();
        $('#'+$(this).attr('id')+"_content").dialog("open");
    });
});
</script>
<h1><?php echo $title ?></h1>
<a class="btn btn-warning"href="<?php echo URL_NOQUERY; ?>?clear=true"><i class="glyphicon glyphicon-trash"></i> <?php echo $lang['admin_error_log_clear']; ?></a>
<?php echo $content; ?>