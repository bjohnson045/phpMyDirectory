<script type="text/javascript">
$(document).ready(function() {
    function resetLocationSetup(element) {
        element.closest(".form-group").nextAll(".form-group").hide();
        element.closest(".form-group").nextAll(".form-group").find("input[type='radio']:checked").each(function() {
            $(this).prop('checked',false);
        });
        element.closest(".form-group").nextAll(".form-group").find("input[type='text']").not("input[id='country_label'],input[id='state_label'],input[id='city_label']").val('');
    }

    $("input[name='country']").click(function() {
        resetLocationSetup($(this));
        if($(this).val() == 'dynamic') {
            $("#country_type-control-group").show();
            $("#state-control-group").hide();
        } else {
            $("#country_static-control-group").show();
            $("#state-control-group").show();
        }
    });

    $("input[name='country_type']").click(function() {
        resetLocationSetup($(this));
        if($(this).val() == 'list') {
            $("#country_option-control-group").show();
        }
        $("#state-control-group").show();
    });

    $("input[name='state']").click(function() {
        resetLocationSetup($(this));
        if($(this).val() == 'dynamic') {
            $("#state_type-control-group").show();
            $("#city-control-group").hide();
        } else {
            $("#state_static-control-group").show();
            $("#city-control-group").show();
        }
    });

    $("input[name='state_type']").click(function() {
        resetLocationSetup($(this));
        if($(this).val() == 'list') {
            $("#state_option-control-group").show();
        }
        if($("input[name='state']:checked").val() == 'dynamic') {
            $("#city_type-control-group").show();
            $("input[name='city']").filter("[value='dynamic']").attr('checked', true);
        } else {
            $("#city-control-group").show();
        }
    });

    $("input[name='city']").click(function() {
        resetLocationSetup($(this));
        if($(this).val() == 'dynamic') {
            $("#city_type-control-group").show();
        } else {
            $("#city_static-control-group").show();
        }
    });

    $("input[name='city_type']").click(function() {
        resetLocationSetup($(this));
        if($(this).val() == 'list') {
            $("#city_option-control-group").show();
        }
        if($("input[name='country']:checked").val() == 'dynamic') {
            $("#country_label-control-group").show();
        }
        if($("input[name='state']:checked").val() == 'dynamic') {
            $("#state_label-control-group").show();
        }
        if($("input[name='city']:checked").val() == 'dynamic') {
            $("#city_label-control-group").show();
        }
    });
});
</script>
<h1><?php echo $lang['admin_locations_wizard']; ?></h1>
<?php echo $form; ?>

