<?php if($form) { ?><form class="form-inline" id="table_list_form" name="table_list_form" method="post"><?php } ?>
    <script type="text/javascript">
    $(document).ready(function() {
        $("#table_page_dropdown a").click(function(e) {
            e.preventDefault();
            window.location = "<?php echo rebuild_url(array(),array('page'),true); ?>page="+$(this).html();
        });
        <?php if($sortable) { ?>
            $(".sortable").sortable({
                cursor: "move",
                handle: ".handle",
                containment: "parent",
                tolerance: "pointer",
                axis: "y",
                opacity: 0.5,
                forcePlaceholderSize: true,
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.css('border','1px solid #ccc');
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function() {
                    showLoadingMessage();
                    $.ajax({ data: ({ action: "update_ordering_inline", table: "<?php echo $sortable_table; ?>", order: $('.sortable').sortable("toArray")}), success:
                        function(data){
                            hideLoadingMessage();
                        }
                    });
                }
            }).disableSelection();
        <?php } ?>
    });
    </script>
    <div class="table_container">
        <table class="table table-striped table-curved table-hover" summary="<?php echo $table_summary; ?>">
            <?php if(!$all_one_page) { ?>
                <caption>
                    <span class="page-numbers-results"><?php echo $page['total_results']; ?> <?php echo $lang['admin_general_table_list_found']; ?>, <?php echo $lang['admin_general_table_list_page']; ?> <?php echo $page['current_page']; ?> <?php echo $lang['admin_general_table_list_of']; ?> <?php echo $page['total_pages']; ?></span>
                    <span class="page-numbers">
                        <div class="btn-group">
                            <a class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $lang['admin_general_table_list_page']; ?> <span class="caret"></span></a>
                            <ul id="table_page_dropdown" class="dropdown-menu">
                                <?php foreach($page['page_numbers'] AS $number) { ?>
                                    <li><a href=""><?php echo $number['number']; ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </span>
                </caption>
            <?php } ?>
            <thead>
                <tr>
                    <?php if($checkbox_value) { ?>
                        <script type="text/javascript">
                        $(document).ready(function() {
                            $("#table_list_checkall").click(function() {
                                var checked_status = this.checked;
                                $('#table_list_checkall').closest('.table_container').find('input[name="table_list_checkboxes[]"]').each(function() {
                                    this.checked = checked_status;
                                });
                            });
                        });
                        </script>
                        <th scope="col" class="table-list-checkbox text-center" nowrap><input type="checkbox" id="table_list_checkall"></th>
                    <?php } ?>
                    <?php if($sortable) { ?>
                        <th scope="col"><?php echo $sortable_label; ?></th>
                    <?php } ?>
                    <?php foreach($columns as $column) { ?>
                        <th scope="col" nowrap<?php if(!is_null($column['style'])) { ?> style="<?php echo $column['style']; ?>"<?php } ?>>
                        <?php if($column['sort_url']) { ?>
                            <a href="<?php echo $column['sort_url']; ?>"><?php echo $column['title']; ?></a>
                            <?php if($column['sort_image']) { ?>
                                <?php if($column['sort_image'] == 'up') { ?>
                                    <i class="glyphicon glyphicon-arrow-up"></i>
                                <?php } else { ?>
                                    <i class="glyphicon glyphicon-arrow-down"></i>
                                <?php } ?>
                            <?php } ?>
                        <?php } else { ?>
                            <?php echo $column['title']; ?>
                        <?php } ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tfoot>
                <?php if($checkbox_options AND $records) { ?>
                    <tr>
                        <th scope="row" colspan="<?php echo ($checkbox_options ? count($columns) + 1 : count($columns)); ?>">
                            <label><?php echo $lang['admin_general_table_list_with_selected']; ?>:</label>
                            <?php foreach($checkbox_options AS $key=>$value) { ?>
                                <span id="<?php echo $value['name']; ?>_container">
                                <?php if($key == 'select') { ?>
                                    <select class="form-control" name="<?php echo $value['name']; ?>" onchange="<?php echo $value['onchange']; ?>">
                                    <?php foreach($value['options'] AS $option_key=>$option_value) { ?>
                                        <option value="<?php echo $option_key; ?>"><?php echo $option_value; ?></option>
                                    <?php } ?>
                                    </select>
                                <?php } elseif($key == 'checkbox') { ?>
                                    <?php if($value['label'] != '') { ?>
                                        <?php echo $value['label']; ?>:
                                    <?php } ?>
                                    <input class="checkbox" type="checkbox" name="<?php echo $value['name']; ?>" value="<?php echo $value['value']; ?>">
                                <?php } ?>
                                </span>
                            <?php } ?>
                            <input class="btn btn-default" type="submit" value="<?php echo $lang['admin_submit']; ?>" name="table_list_submit" id="table_list_submit">
                        </th>
                    </tr>
                <?php } ?>
            </tfoot>
            <tbody<?php if($sortable) { ?> class="sortable"<?php } ?>>
                <?php if(count($records) > 0) { ?>
                    <?php foreach($records as $key=>$record) { ?>
                        <tr id="table_id_<?php echo $record['id']; ?>">
                            <?php if($sortable) { ?><td class="text-center"><i class="glyphicon glyphicon-resize-vertical handle"></i></td><?php } ?>
                            <?php if($checkbox_value) { ?>
                                <td class="text-center"><input class="table-list-checkbox-input" type="checkbox" name="table_list_checkboxes[]" value="<?php echo $record[$checkbox_value]; ?>"></td>
                            <?php } ?>
                            <?php if($columns_template or false) { ?>
                                <?php include($columns_template); ?>
                            <?php } else { ?>
                                <?php foreach($columns as $key=>$column) { ?>
                                    <?php if($key == 0) { ?>
                                        <th scope="row" id="<?php echo $key; ?>"<?php if($column['nowrap']) { ?> nowrap<?php }?>><?php echo $record[$columns[0]['name']]; ?></th>
                                    <?php } else { ?>
                                        <td<?php if($column['nowrap']) { ?> nowrap<?php }?>><?php echo $record[$column['name']]; ?></td>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <th colspan="<?php echo ($checkbox_value ? count($columns) + 1 : count($columns)); ?>" scope="row"></th>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php if(!$all_one_page) { ?>
            <div class="row table_footer">
                <div class="col-md-5">
                    <?php echo $lang['admin_general_table_list_results']; ?> <strong><?php echo $page['start_offset']; ?> - <?php echo $page['end_offset']; ?></strong> of <?php echo $page['total_results']; ?>
                </div>
                <div class="col-md-14 text-center">
                    <?php echo $page_navigation; ?>
                </div>
                <div class="col-md-5">
                    <?php if($page['page_sizes']) { ?>
                        <?php echo $lang['admin_general_table_list_show']; ?>:
                        <?php foreach($page['page_sizes'] AS $size=>$url) { ?>
                            <a href="<?php echo $url; ?>"<?php if($page['page_size'] == $size) { ?> style="font-weight: bold;"<?php } ?>><?php echo $size; ?></a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php if($form) { ?></form><?php } ?>
