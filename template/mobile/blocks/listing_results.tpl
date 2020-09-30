<ul data-role="listview" style="margin-bottom: 5px;">
    <li data-role="list-divider" role="heading">
        Listings
        <span class="page-numbers-results">
            <?php echo $lang['public_search_results_results']; ?> <strong><?php echo $page['start_offset']; ?> - <?php echo $page['end_offset']; ?></strong> <?php echo $lang['public_general_table_list_of']; ?> <?php echo $page['total_results']; ?>
        </span>
    </li>
    <?php echo $listing_results; ?>
</ul>
<?php echo $page_navigation; ?>