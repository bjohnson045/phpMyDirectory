<h1><?php echo $lang['admin_languages_replace']; ?></h1>
<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <?php echo $form->getFieldGroup('id'); ?>
    <?php echo $form->getFieldGroup('find'); ?>
    <?php echo $form->getFieldGroup('replace'); ?>
    <?php echo $form->getFieldGroup('section'); ?>
    <?php echo $form->getFieldGroup('case_sensitive'); ?>
    <?php echo $form->getFieldGroup('match_exact'); ?>
    <?php echo $form->getFieldGroup('translated'); ?>
    <?php echo $form->getFormActions(); ?>
</fieldset>
<?php if(isset($table_list)) { ?>
    <?php echo $table_list; ?>
<?php } ?>
<?php echo $form->getFormCloseHTML(); ?>
<script type="text/javascript">
$(document).ready(function(){
    $('#table_list_checkall').trigger('click');
    $('#table_list_checkall').prop('checked', true);
});
</script>