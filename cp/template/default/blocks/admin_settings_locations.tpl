<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_default_country'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_default_country'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('locations_num_columns'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('locations_num_columns'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('locations_vertical_sort'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('locations_vertical_sort'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('loc_empty_hidden'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('loc_empty_hidden'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('loc_show_indexes'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('loc_show_indexes'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('loc_show_subs_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('loc_show_subs_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('show_location_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('show_location_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('show_location_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('show_location_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('location_browse_children'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('location_browse_children'); ?>
        </div>
    </div>
</fieldset>
<script type="text/javascript">
$(document).ready(function(){
    var location_labels = new Array();
    location_labels['location_text_1'] = '<?php echo $lang['general_locations_text_1']; ?>';
    location_labels['location_text_2'] = '<?php echo $lang['general_locations_text_2']; ?>';
    location_labels['location_text_3'] = '<?php echo $lang['general_locations_text_3']; ?>';
    $('#location_text_1, #location_text_2, #location_text_3').change(function(){
        if($(this).is(':checked')) {
            $('#map_city, #map_state, #map_country').append($("<option></option>").attr("value",$(this).attr('id')).text(location_labels[$(this).attr('id')]));
        } else {
            $('#map_city, #map_state, #map_country').children("option[value='"+$(this).attr('id')+"']").remove();
        }
    });
});
</script>
<fieldset>
    <legend>Address Formatting</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('location_text_1'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('location_text_1'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('location_text_2'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('location_text_2'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('location_text_3'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('location_text_3'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_city'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_city'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_state'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_state'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_country'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_country'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_city_static'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_city_static'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_state_static'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_state_static'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_country_static'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_country_static'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Maps</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_type'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_display_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_display_type'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_zoom'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_zoom'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_select_coordinates'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_select_coordinates'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_select_zoom'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_select_zoom'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('mapquest_apikey'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('mapquest_apikey'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Other</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('geocoding_service'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('geocoding_service'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('geolocation_fill'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('geolocation_fill'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('html_editor_locations'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('html_editor_locations'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('location_select_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('location_select_type'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_search_engine_optimization']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title_location_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_location_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_location_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_location_default'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>