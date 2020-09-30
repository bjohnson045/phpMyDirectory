<div class="row">
    <div class="col-lg-12">
        <?php echo $form->getFormOpenHTML(array('class'=>'form-inline')); ?>
            <?php echo $form->getFieldHTML('keyword'); ?><?php echo $form->getFieldHTML('submit'); ?>
        <?php echo $form->getFormCloseHTML(); ?>
    </div>
</div>
<br>
<?php if($user_count) { ?>
    <?php echo $user_results; ?>
<?php } else { ?>
    <?php echo $lang['public_search_results_no_results']; ?>
<?php } ?>