<script type="text/javascript">
$(document).ready(function(){
    if($('#type').val() == 'listings') {
        $('#listing_variables').show();
    }
    $('#type').change(function(){
        if($(this).val() == 'users') {
            $('#listing_variables').hide();
        } else if($(this).val() == 'listings') {
            $('#listing_variables').show();
        }
    });
});
</script>
<li class="list-group-item">
    <strong><?php echo $lang['admin_email_campaigns_variables']; ?>:</strong><br />
    <?php foreach((array) $general_variables as $variable) { ?>
        *<?php echo $variable; ?>*<br />
    <?php } ?>
    <br /><strong><?php echo $lang['admin_email_campaigns_variables_user']; ?>:</strong><br />
    <?php foreach((array) $user_variables as $variable) { ?>
        *<?php echo $variable; ?>*<br />
    <?php } ?>
    <div id="listing_variables" style="display: none">
        <br /><strong><?php echo $lang['admin_email_campaigns_variables_listing']; ?>:</strong><br />
        <?php foreach((array) $listing_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    </div>
</li>