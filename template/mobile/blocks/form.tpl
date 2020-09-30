<div class="form-container">
    <?php echo $open; ?>
    <?php foreach($fieldsets AS $fieldset) { ?>
        <?php echo $fieldset; ?>
    <?php } ?>
    <?php if($actions) { ?>
        <fieldset class="buttonrow">
            <?php echo $actions; ?>
        </fieldset>
    <?php } ?>
    <?php echo $hidden_fields; ?>
    <?php echo $close; ?>
</div>