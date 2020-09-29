<script type="text/javascript">
$(document).ready(function() {
    $("#user_country").change(function() {
        if($(this).val() == 'United States') {
            $("#user_state_select-control-group").show();
            $("#user_state-control-group").hide();
        } else {
            $("#user_state_select-control-group").hide();
            $("#user_state-control-group").show();
        }
    });
    $("#user_state_select").change(function() {
        $("#user_state").val($(this).val());
    });
    $("#user_country").trigger("change");
    $("#user_state_select").val($("#user_state").val());

    $('input[id^="user_groups_"]').change(function() {
        if(this.checked) {
            $.ajax({
                data: ({
                    action: 'admin_user_groups_warning',
                    id: $(this).val()
                }),
                success: function(administrator) {
                    if(administrator == 1) {
                        bootbox.alert('<?php echo $lang['admin_users_admin_permission_notice']; ?>');
                    }
                }
            });
        }
    });
});
</script>
<?php echo $users_summary_header; ?>
<h2><?php echo $title; ?></h2>
<?php echo $content; ?>