<p style="margin-top: 0px">
    <?php echo $lang['public_search_results_search']; ?>
    <?php if($keyword) { ?>
        <?php echo $lang['public_search_results_for']; ?> "<?php echo $this->escape($keyword); ?>"
    <?php } ?>
    <?php if($location OR $category) { ?>
        <?php echo $lang['public_search_results_in']; ?> <?php echo $this->escape($location); ?> <?php echo $this->escape($category); ?>
    <?php } ?>
    <?php if($zip_miles) { ?>
        <?php echo $lang['public_search_results_within']; ?> <?php echo $this->escape($zip_miles); ?> <?php echo $lang['public_general_search_miles']; ?>
    <?php } ?>
</p>
<?php if($spelling_suggestion) { ?>
    <p><?php echo $lang['public_search_results_spelling']; ?> <a href="<?php echo $spelling_suggestion_url; ?>"><?php echo $spelling_suggestion; ?></a>?</p>
<?php } ?>
<?php if($listing_count) { ?>
    <p style="padding-bottom: 10px;"><?php echo $lang['public_search_results_results']; ?>: <b><?php echo $listing_count; ?></b></p>
    <?php echo $listing_results; ?>
<?php } elseif(count($matching_categories) > 0) { ?>
    <h2><?php echo $lang['public_search_results_matching_categories']; ?>:</h2>
    <?php foreach($matching_categories as $category) { ?>
        <a href="<?php echo $category['url']; ?>"><?php echo $this->escape($category['title']); ?></a> (<?php echo $category['count_total']; ?>)<br />
    <?php } ?>
<?php } else { ?>
    <p><?php echo $lang['public_search_results_no_results']; ?></p>
<?php } ?>
