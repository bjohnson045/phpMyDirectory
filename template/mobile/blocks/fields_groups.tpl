<?php foreach($custom_fields_groups as $custom_fields_group) { ?>
    <strong><?php echo $this->escape($custom_fields_group['title']); ?></strong><br />
    <?php foreach($custom_fields_group['fields'] AS $field) { ?>
        <?php if(${"custom_".$field['id']} != '') { ?>
            <?php echo $this->escape($field['name']); ?>: <?php echo ${"custom_".$field['id']}; ?><br />
        <?php } ?>
    <?php } ?>
    <br />
<?php } ?>