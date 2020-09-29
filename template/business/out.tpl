<script type="text/javascript">
$(document).ready(function() {
    $("#out_disable_0").change(function() {
        $.ajax({
            data: ({
                action: 'out_change',
                out_disable: $("#out_disable_0").prop('checked')
            }),
            success: function() {}
        });
    });
});
</script>
<p><h2><?php echo $lang['public_out_leave']; ?> <?php echo $config['title']; ?></h2></p>

<p><?php echo $lang['public_out_visiting']; ?>:<br>
<strong><?php echo $this->escape($url); ?></strong></p>

<div class="alert alert-warning">
    <?php echo $message; ?>
</div>

<?php if(LOGGED_IN) { ?>
    <?php echo $form->getFieldHTML('out_disable'); ?>
<?php } ?>

<p><a class="btn btn-primary" href="<?php echo $this->escape($url); ?>"><?php echo $lang['continue']; ?></a> <a class="btn btn-default" onclick="window.close(); return false;" href=""><?php echo $lang['cancel']; ?></a></p>