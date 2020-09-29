<script type="text/javascript">
$(document).ready(function() {
    $('#administrator').change(function() {
        $("#administrator_permissions-control-group").toggle(this.checked);
        if(!this.checked) {
            $("#administrator_permissions-control-group").find('input[type=checkbox]:checked').removeAttr('checked');
        }
    });
    $('#user').change(function() {
        if(!this.checked) {
            $('#advertiser').prop('checked', false).trigger('change');
        }
        $("#user_permissions-control-group").toggle(this.checked);
        if(!this.checked) {
            $("#user_permissions-control-group").find('input[type=checkbox]:checked').removeAttr('checked');
        }
    });
    $('#advertiser').change(function() {
        if(this.checked) {
            $('#user').prop('checked', true).trigger('change');
        }
    });
    $('#administrator').trigger("change");
    $('#advertiser').trigger("change");
    $('#user').trigger("change");
});
</script>

<h1><?php echo $title; ?></h1>
<?php echo $content; ?>