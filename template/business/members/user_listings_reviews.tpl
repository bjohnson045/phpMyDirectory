<script type="text/javascript">
$(document).ready(function(){
    $("[id^=review-share-]").each(function() {
        <?php echo $share_button_script; ?>
    });
});
</script>
<table summary="<?php echo $table_summary; ?>" class="table table-bordered table-striped table-responsive">
    <caption>
        <p class="small pull-left"><?php echo $page['total_results']; ?> <?php echo $lang['user_general_table_list_found']; ?>,  <?php echo $lang['user_general_table_list_page']; ?> <?php echo $page['current_page']; ?> <?php echo $lang['user_general_table_list_of']; ?> <?php echo $page['total_pages']; ?></p>
        <p class="small pull-right"><?php echo $lang['user_general_table_list_results']; ?> <?php echo $page['start_offset']; ?> - <?php echo $page['end_offset']; ?> <?php echo $lang['user_general_table_list_of']; ?> <?php echo $page['total_results']; ?></p>
    </caption>
    <thead>
        <tr>
            <?php foreach($columns as $column) { ?>
                <th scope="col" nowrap><?php echo $column['title']; ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php if(count($records) > 0) { ?>
            <?php foreach($records as $key=>$record) { ?>
                <tr>
                    <th scope="row" id="<?php echo $key; ?>"><?php echo $this->escape($record['title']); ?></th>
                    <td><?php echo $lang[$record['status']]; ?></td>
                    <td><?php echo $record['date']; ?></td>
                    <td><?php echo $record['rating_static']; ?></td>
                    <td>
                        <a target="_blank" href="<?php echo BASE_URL; ?>/listing_reviews.php?review_id=<?php echo $record['id']; ?>&action=respond" class="btn btn-default btn-xs"><span class="fa fa-mail-reply"></span> <?php echo $lang['user_listings_reviews_respond']; ?></a>
                        <a target="_blank" href="<?php echo BASE_URL; ?>/listing_reviews.php?review_id=<?php echo $record['id']; ?>" class="btn btn-default btn-xs"><span class="fa fa-eye"></span> <?php echo $lang['user_view']; ?></a>
                        <a data-url="<?php echo BASE_URL; ?>/listing_reviews.php?id=<?php echo $record['id']; ?>" data-title="<?php echo $this->escape($record['title']); ?>" href="" id="review-share-<?php echo $record['id']; ?>" class="btn btn-default btn-xs"><span class="fa fa-share-square-o"></span> <?php echo $lang['user_listings_reviews_share']; ?></a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <th colspan="<?php echo count($columns); ?>" scope="row" style="text-align: center;"><?php echo $empty_content; ?></th>
            </tr>
        <?php } ?>
    </tbody>
</table>
<div class="row table_footer">
    <div class="col-md-12 text-center">
        <?php echo $page_navigation; ?>
    </div>
</div>