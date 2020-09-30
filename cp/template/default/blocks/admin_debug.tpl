<div class="container-fluid well">
<div class="row">
    <div class="<?php if(PMD_SECTION != 'admin') { ?>col-xs-6<? } else { ?>col-xs-12<?php } ?>">
        <div class="panel panel-default">
            <div class="panel-heading">Debug Console</div>
            <ul class="list-group">
                <li class="list-group-item">Query Count <span class="badge"><?php echo $query_count; ?></span></li>
                <li class="list-group-item">Load Time <span class="badge"><?php echo $page_load_time; ?></span></li>
                <li class="list-group-item">Estimated memory start <span class="badge"><?php echo $memory_start; ?></span></li>
                <li class="list-group-item">Estimated memory end <span class="badge"><?php echo $memory_end; ?></span></li>
                <li class="list-group-item">Estimated peak memory <span class="badge"><?php echo $memory_peak; ?></span></li>
                <li class="list-group-item">Current Template: <?php echo $this->escape($current_template); ?></li>
                <li class="list-group-item">Language: <?php echo $this->escape($current_language); ?></li>
            </ul>
        </div>
    </div>
    <div class="<?php if(PMD_SECTION != 'admin') { ?>col-xs-6<? } else { ?>col-xs-12<?php } ?>">
        <div class="panel panel-default">
            <div class="panel-heading">POST Variables</div>
            <table class="table table-striped table-bordered table-responsive">
                <?php foreach($_POST as $key=>$value) { ?>
                <tr>
                    <td><?php echo $key; ?></td>
                    <td>
                        <?php if(is_array($value)) { ?>
                            <br>
                            <?php foreach($value as $key2=>$value2) { ?>
                                <?php echo $key2; ?> => <?php echo $this->escape($value2); ?><br>
                            <?php } ?>
                        <?php } else { ?>
                            <?php echo $this->escape($value); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
<div class="row">
    <div class="<?php if(PMD_SECTION != 'admin') { ?>col-xs-12<? } else { ?>col-xs-24<?php } ?>">
        <div class="panel panel-default">
            <div class="panel-heading">Queries</div>
            <table class="table table-striped table-bordered table-responsive">
                <tr>
                    <th>Query</th>
                    <th>Time/Seconds</th>
                    <th>Variables</th>
                </tr>
                <?php foreach($query_list as $key=>$value) { ?>
                    <tr>
                        <td>
                            <?php echo $value['query']; ?>
                        </td>
                        <td>
                            <?php if(floatval($value['time']) > 0.01) { ?>
                                <font color="red"><b><?php echo $value['time']; ?></b></font>
                            <?php } else { ?>
                                <?php echo $value['time']; ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php foreach($value['parameters'] AS $param_key=>$param_value) { ?>
                                <?php echo $param_key; ?>: <?php echo $param_value; ?><br>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
<div class="row">
    <div class="<?php if(PMD_SECTION != 'admin') { ?>col-xs-6<? } else { ?>col-xs-12<?php } ?>">
        <div class="panel panel-default">
            <div class="panel-heading">Session Variables</div>
            <table class="table table-striped table-bordered table-responsive">
                <?php foreach($_SESSION as $key=>$value) { ?>
                <tr>
                    <td><?php echo $key; ?></td>
                    <td>
                        <?php if(is_array($value)) { ?>
                            <?php foreach($value as $key2=>$value2) { ?>
                                <?php echo $key2; ?> => <?php echo $this->escape($value2); ?><br>
                            <?php } ?>
                        <?php } else { ?>
                            <?php echo $this->escape($value); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
    <div class="<?php if(PMD_SECTION != 'admin') { ?>col-xs-6<? } else { ?>col-xs-12<?php } ?>">
        <div class="panel panel-default">
            <div class="panel-heading">COOKIE Variables</div>
            <table class="table table-striped table-bordered table-responsive">
                <?php foreach($_COOKIE as $key=>$value) { ?>
                <tr>
                    <td><?php echo $key; ?></td>
                    <td style="overflow-wrap: break-word; word-wrap: break-word; word-break: break-word;">
                        <?php if(is_array($value)) { ?>
                            <br>
                            <?php foreach($value as $key2=>$value2) { ?>
                                <?php echo $key2; ?> => <?php echo $this->escape($value2); ?><br>
                            <?php } ?>
                        <?php } else { ?>
                            <?php echo $this->escape($value); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>