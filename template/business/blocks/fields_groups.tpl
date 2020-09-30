<?php foreach($custom_fields_groups as $custom_fields_group) { ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $this->escape($custom_fields_group['title']); ?></h3>
        </div>
        <div class="panel-body">
            <?php foreach($custom_fields_group['fields'] AS $field) { ?>
                <?php if(${"custom_".$field['id']} != '') { ?>
                    <p><strong><?php echo $this->escape($field['name']); ?>:</strong> <?php echo ${"custom_".$field['id']}; ?></p>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
<?php } ?>