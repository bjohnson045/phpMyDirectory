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
                    <th scope="row" id="<?php echo $key; ?>"><a href="./user_messages_posts.php?message_id=<?php echo $record['id']; ?>"><?php echo $this->escape($record['title']); ?></th>
                    <td><?php echo $this->escape($record['user_from']); ?></td>
                    <td><?php echo $this->escape($record['user_to']); ?></td>
                    <td>
                        <a href="<?php echo URL_NOQUERY; ?>?action=delete&id=<?php echo $record['id']; ?>" onclick="return confirm('<?php echo $lang['messages_confirm']; ?>');" class="btn btn-default btn-xs"><span class="fa fa-times text-danger"></span> <?php echo $lang['user_delete']; ?></a>
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