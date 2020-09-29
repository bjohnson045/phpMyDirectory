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
                    <th scope="row" id="<?php echo $key; ?>"><?php echo $record['id']; ?></th>
                    <td><?php echo $record['order_id']; ?></td>
                    <td><?php echo $record['date']; ?></td>
                    <td><?php echo $record['date_due']; ?></td>
                    <td><?php echo $record['total'] ;?></td>
                    <td>
                        <span class="label <?php if($record['balance'] > 0.00) { ?>label-danger<?php } else { ?>label-success<?php } ?>"><?php echo format_number_currency($record['balance']); ?></span>
                    </td>
                    <td>
                        <?php if($record['status'] == 'unpaid') { ?>
                            <span class="text-danger"><?php echo $lang['user_invoices_'.$record['status']]; ?></span>
                            <?php if(trim($record['subscription_id']) != '') { ?>
                                (<?php echo $lang['user_invoices_paid_by_subscription']; ?>)
                            <?php } ?>
                        <?php } elseif($record['status'] == 'paid') { ?>
                            <span class="text-success"><?php echo $lang['user_invoices_'.$record['status']]; ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($record['status'] == 'unpaid') { ?>
                            <a class="btn btn-default btn-xs" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices_pay.php?id=<?php echo $record['id']; ?>"><span class="fa fa-money"></span> <?php echo $lang['user_invoices_pay_invoice']; ?></a>
                        <?php } ?>
                        <a href="javascript:newWindow('<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices_print.php?id=<?php echo $record['id']; ?>','popup',650,600,'')" class="btn btn-default btn-xs"><span class="fa fa-print"></span> <?php echo $lang['user_invoices_print']; ?></a>
                        <a href="user_invoices.php?action=pdf&id=<?php echo $record['id']; ?>" class="btn btn-default btn-xs"><span class="fa fa-download"></span> <?php echo $lang['user_invoices_download_pdf']; ?></a>
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