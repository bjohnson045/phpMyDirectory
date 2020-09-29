<table summary="<?php echo $table_summary; ?>" class="table table-bordered table-striped">
    <caption>
        <p class="small pull-left"><?php echo $page['total_results']; ?> <?php echo $lang['user_general_table_list_found']; ?>,  <?php echo $lang['user_general_table_list_page']; ?> <?php echo $page['current_page']; ?> <?php echo $lang['user_general_table_list_of']; ?> <?php echo $page['total_pages']; ?></p>
        <?php if(isset($page['page_select'])) { ?>
            <p class="pull-right"><?php echo $lang['user_general_table_list_page']; ?>: <?php echo $page['page_select']; ?></p>
        <?php } ?>
    </caption>
    <thead>
        <tr>
            <?php foreach($columns as $column) { ?>
                <th scope="col" nowrap><?php echo $column['title']; ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th scope="row" colspan="<?php echo count($columns); ?>">
                <span class="pull-left small"><?php echo $lang['user_general_table_list_results']; ?> <?php echo $page['start_offset']; ?> - <?php echo $page['end_offset']; ?> <?php echo $lang['user_general_table_list_of']; ?> <?php echo $page['total_results']; ?></span>
                <div class="pull-right"><?php echo $page_navigation; ?></div>
            </th>
        </tr>
    </tfoot>
    <tbody>
        <?php if(count($records) > 0) { ?>
            <?php foreach($records as $key=>$record) { ?>
                <tr<?php if($key%2) { ?> class="odd"<?php } ?>>
                    <?php foreach($columns as $key=>$column) { ?>
                        <?php if($key == 0) { ?>
                            <th scope="row" id="<?php echo $key; ?>"><?php echo $record[$columns[0]['name']]; ?></th>
                        <?php } else { ?>
                            <td<?php if($column['nowrap']) { ?> nowrap<?php }?>><?php echo $record[$column['name']]; ?></td>
                        <?php } ?>
                    <?php } ?>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <th colspan="<?php echo count($columns); ?>" scope="row" style="text-align: center;"><?php echo $empty_content; ?></th>
            </tr>
        <?php } ?>
    </tbody>
</table>