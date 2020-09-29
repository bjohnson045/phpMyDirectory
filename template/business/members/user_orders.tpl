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
                    <th scope="row" id="<?php echo $key; ?>"><?php echo $record['order_id']; ?></th>
                    <td><?php echo $record['date']; ?></td>
                    <td>
                        <strong><?php echo $this->escape($record['product']); ?></strong>
                        <?php if(isset($record['product_title'])) { ?>
                            <br>
                            <?php if(isset($record['product_url'])) { ?>
                                <a href="<?php echo $record['product_url']; ?>" target="_blank"><?php echo $this->escape($record['product_title']); ?></a>
                            <?php } else { ?>
                                <?php echo $this->escape($record['product_title']); ?>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($record['overdue']) { ?>
                            <span class="text-danger"><?php echo $record['next_due_date']; ?></span>
                        <?php } else { ?>
                            <?php echo $record['next_due_date']; ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($record['status'] == 'active') { ?>
                            <span class="text-success"><?php echo $lang[$record['status']]; ?></span>
                        <?php } else { ?>
                            <?php echo $lang[$record['status']]; ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($record['product_status'] == 'active') { ?>
                            <span class="text-success"><?php echo $lang[$record['product_status']]; ?></span>
                        <?php } else { ?>
                            <?php echo $lang[$record['product_status']]; ?>
                        <?php } ?>
                        <?php if($record['product_pending_approval']) { ?> (<?php echo $lang['user_listings_pending_approval']; ?>)<?php } ?></td>
                    <td>
                        <a class="btn btn-default btn-xs" href="user_orders_view.php?id=<?php echo $record['id']; ?>"><span class="fa fa-eye"></span> <?php echo $lang['user_orders_view']; ?></a>
                        <?php if($record['type'] == 'listing_membership') { ?>
                            <a class="btn btn-default btn-xs" href="user_listings_summary.php?action=edit&id=<?php echo $record['type_id']; ?>&user_id=<?php echo $record['user_id']; ?>"><span class="fa fa-pencil"></span> <?php echo $lang['user_orders_manage_listing']; ?></a>
                        <?php } ?>
                        <?php if($record['renew']) { ?>
                            <a class="btn btn-default btn-xs" href="user_orders.php?action=renew&id=<?php echo $record['id']; ?>"><?php echo $lang['user_orders_renew']; ?></a>
                        <?php } ?>
                        <a class="btn btn-default btn-xs" href="user_orders.php?action=copy&id=<?php echo $record['id']; ?>"><span class="fa fa-files-o"></span> <?php echo $lang['user_orders_copy']; ?></a>
                        <a class="btn btn-default btn-xs" href="user_cancellations.php?id=<?php echo $record['id']; ?>"><span class="fa fa-times text-danger"></span> <?php echo $lang['cancel']; ?></a>
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