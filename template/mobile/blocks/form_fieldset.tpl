<fieldset <?php echo $fieldset_attributes; ?>>
    <?php if($legend) { ?>
        <legend><?php echo $legend; ?></legend>
    <?php } ?>
    <ol>
        <?php foreach($fields AS $field) { ?>
            <?php echo $field; ?>
        <?php } ?>
    </ol>
</fieldset>