<?php echo $listing_results; ?>
<div class="row">
    <div class="col-lg-4 hidden-xs">
        <?php echo $lang['public_search_results_results']; ?> <strong><?php echo $page['start_offset']; ?> - <?php echo $page['end_offset']; ?></strong> <?php echo $lang['public_general_table_list_of']; ?> <?php echo $page['total_results']; ?>
    </div>
    <div class="col-lg-8">
        <?php echo $page_navigation; ?>
    </div>
</div>