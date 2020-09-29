<div class="panel panel-default" <?php echo $fieldset_attributes; ?>>
    <?php if($legend) { ?>
        <div class="panel-heading"><?php echo $legend; ?></div>
    <?php } ?>
    <div class="panel-body">
        <?php foreach($fields AS $field) { ?>
            <?php echo $field; ?>
        <?php } ?>
    </div>
</div>