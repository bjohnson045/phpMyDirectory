<?php echo $events_results; ?>
<div class="row">
    <div class="col-lg-12">
        <div class="pull-left">
            <?php echo $lang['public_search_results_results']; ?> <strong><?php echo $page['start_offset']; ?> - <?php echo $page['end_offset']; ?></strong> <?php echo $lang['public_general_table_list_of']; ?> <?php echo $page['total_results']; ?>
        </div>
        <div class="text-center">
            <?php echo $page_navigation; ?>
        </div>
    </div>
</div>