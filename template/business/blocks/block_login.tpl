<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $this->escape($title); ?></h3>
    </div>
    <div class="panel-body">
        <?php echo $form->getFormOpenHTML(array('class'=>'form')); ?>
        <div class="form-group">
            <?php echo $form->getFieldHTML('user_login',array('placeholder'=>$lang['block_login_username'])); ?>
        </div>
        <div class="form-group">
            <?php echo $form->getFieldHTML('user_pass',array('placeholder'=>$lang['block_login_password'])); ?>
        </div>
        <?php echo $form->getFieldHTML('remember'); ?>
        <?php echo $form->getFieldHTML('submit_login'); ?>
        <?php echo $form->getFormCloseHTML(); ?>
        <p><small><a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_password_remind.php"><?php echo $lang['block_login_forgot_password']; ?></a></small></p>
    </div>
</div>