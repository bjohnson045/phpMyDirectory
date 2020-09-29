<?php echo $open; ?>
<?php foreach($fieldsets AS $fieldset) { ?>
    <?php echo $fieldset; ?>
<?php } ?>
<?php if($actions) { ?>
    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <?php echo $actions; ?>
        </div>
    </div>
<?php } ?>
<?php echo $hidden_fields; ?>
<?php echo $close; ?>