<h1><?php echo $lang['admin_languages_phrases']; ?></h1>
<?php echo $form; ?>
<?php echo $form_edit->getFormOpenHTML(); ?>
<?php echo $table_list; ?>
<div class="btn-toolbar">
    <?php echo $form_edit->getFieldHTML('edit_phrases'); ?>
    <?php if($updated) { ?>
        <a class="btn btn-warning" href="admin_phrases.php?action=clear_updated">Accept Updated Phrases</a>
    <?php } ?>
</div>
<?php echo $form_edit->getFormCloseHTML(); ?>