<script type="text/javascript">
$(document).ready(function() {
    $("#plugin_select").change(function(){ window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_plugins_edit.php?id="+$(this).val()+"&file="+$(this).val()+".php"); });
});
</script>
<h1><?php echo $lang['admin_plugins_edit']; ?></h1>
<h2><?php echo $file; ?></h2>
<?php if(isset($form)) { ?>
    <?php echo $form->getFormOpenHTML(); ?>
        <?php echo $form->getFieldHTML('code'); ?><br /><br />
        <?php echo $form->getFieldHTML('submit'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
<?php } ?>
<?php if(isset($image)) { ?>
    <img src="<?php echo $image; ?>">
<?php } ?>