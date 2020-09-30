<?php if($keyword OR $location OR $category OR $zip_miles) { ?>
    <?php echo $lang['public_search_results_search']; ?>
<?php } ?>
<?php if($keyword) { ?>
    <?php echo $lang['public_search_results_for']; ?> "<?php echo $this->escape($keyword); ?>"
<?php } ?>
<?php if($location OR $category) { ?>
    <?php echo $lang['public_search_results_in']; ?> <?php echo $this->escape($location); ?> <?php echo $this->escape($category); ?>
<?php } ?>
<?php if($zip_miles) { ?>
    <?php echo $lang['public_search_results_within']; ?> <?php echo $this->escape($zip_miles); ?> <?php echo $lang['public_general_search_miles']; ?>
<?php } ?>
<?php if($spelling_suggestion) { ?>
    <p><?php echo $lang['public_search_results_spelling']; ?> <a href="<?php echo $spelling_suggestion_url; ?>"><?php echo $spelling_suggestion; ?></a>?</p>
<?php } ?>
<?php if($listing_count) { ?>
    <?php if($listing_count > 1) { ?>
    <script>
    $(document).ready(function(){
        $('#search_results_sort').on('change', function () {
            var parameters = $(this).val();
            if(parameters) {
                parameters = parameters.split(':');
                if(parameters.length == 2) {
                    window.location = "<?php echo $search_results_order_url; ?>sort_order="+parameters[0]+"&sort_direction="+parameters[1];
                }
            }
            return false;
        });
        $('#search_results_sort').val("<?php echo $this->escape($search_results_order); ?>");
    });
    </script>
    <div class="form-inline pull-right">
        <label><?php echo $lang['public_search_results_sort']; ?>: </label>
        <select id="search_results_sort" class="form-control input-sm">
            <option value=""></option>
            <option value="date:DESC"><?php echo $lang['public_search_results_sort_newest']; ?></option>
            <option value="rating:DESC"><?php echo $lang['public_search_results_sort_rated_highest']; ?></option>
            <option value="score:DESC"><?php echo $lang['public_search_results_sort_relevancy']; ?></option>
            <option value="impressions:DESC"><?php echo $lang['public_search_results_sort_impressions']; ?></option>
            <option value="title:ASC"><?php echo $lang['public_search_results_sort_title']; ?></option>
            <option value="date_update:DESC"><?php echo $lang['public_search_results_sort_updated']; ?></option>
        </select>
    </div>
    <?php } ?>
    <p><?php echo $lang['public_search_results_results']; ?>: <b><?php echo $listing_count; ?></b></p>
    <?php echo $map; ?>
    <?php echo $listing_results; ?>
<?php } elseif(count($matching_categories) > 0 OR count($matching_locations) > 0) { ?>
    <?php if(count($matching_categories) > 0) { ?>
        <h2><?php echo $lang['public_search_results_matching_categories']; ?>:</h2>
        <?php foreach($matching_categories as $category) { ?>
            <a href="<?php echo $category['url']; ?>"><?php echo $this->escape($category['title']); ?></a> (<?php echo $category['count_total']; ?>)<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($matching_locations) > 0) { ?>
        <h2><?php echo $lang['public_search_results_matching_locations']; ?>:</h2>
        <?php foreach($matching_locations as $location) { ?>
            <a href="<?php echo $location['url']; ?>"><?php echo $this->escape($location['title']); ?></a> (<?php echo $location['count_total']; ?>)<br />
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <p><?php echo $lang['public_search_results_no_results']; ?></p>
<?php } ?>