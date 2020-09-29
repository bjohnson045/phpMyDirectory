<fieldset <?php echo $fieldset_attributes; ?>>
    <?php if($legend) { ?>
        <legend><?php echo $legend; ?></legend>
    <?php } ?>
    <?php foreach($fields AS $field) { ?>
        <?php echo $field; ?>
    <?php } ?>
</fieldset>