<div style="padding: 5px 0">
    <?php echo $lang['user_index_not_a_members']; ?> <a href="<?php echo $create_account_url; ?>"><?php echo $lang['user_index_create_account']; ?></a>
</div>

<?php echo $form->getFormOpenHTML(); ?>
<?php echo $form->getFieldLabel('user_login'); ?><?php echo $form->getFieldHTML('user_login'); ?>
<?php echo $form->getFieldLabel('user_pass'); ?><?php echo $form->getFieldHTML('user_pass'); ?>
<?php echo $form->getFieldHTML('remember'); ?>
<?php if($remote_login) { ?>
    <li><label>&nbsp;</label><a id="remote_login_link" href="#"><?php echo $lang['user_index_remote_login']; ?></a></li>
<?php } ?>

    <?php echo $form->getFieldHTML('submit_login',array('data-inline'=>'true')); ?>
    <a data-role="button" data-inline="true" href="<?php echo $password_reminder_url; ?>"><?php echo $lang['user_index_password_reminder']; ?></a>
<?php echo $form->getFormCloseHTML(); ?>

<script type="text/javascript">
<!-- document.login.login.focus(); //-->
</script>