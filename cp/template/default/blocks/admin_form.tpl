<?php echo $open; ?>
<?php foreach($fieldsets AS $fieldset) { ?>
    <?php echo $fieldset; ?>
<?php } ?>
<?php if($actions) { ?>
    <?php echo $actions; ?>
<?php } ?>
<?php if(isset($hidden_fields)) { ?>
    <?php echo $hidden_fields; ?>
<?php } ?>
<?php echo $close; ?>
