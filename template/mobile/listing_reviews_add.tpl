<h3 style="margin-top: 0px"><?php echo $this->escape($listing['title']); ?></h3>
<?php if($log_in_url) { ?>
    <?php echo $lang['public_listing_reviews_have_account'];?> <a href="<?php echo $log_in_url; ?>"><?php echo $lang['public_listing_reviews_login']; ?></a>
<?php } ?>
<?php echo $form->getFormOpenHTML(); ?>
<?php if($form->fieldExists('name')) { ?>
    <?php echo $form->getFieldLabel('name'); ?><?php echo $form->getFieldHTML('name'); ?>
<?php } ?>
<?php echo $form->getFieldLabel('title'); ?><?php echo $form->getFieldHTML('title'); ?>
<?php echo $form->getFieldLabel('rating'); ?><?php echo $form->getFieldHTML('rating'); ?>
<?php echo $form->getFieldLabel('review'); ?><?php echo $form->getFieldHTML('review'); ?>
<p class="note counter"><?php echo $form->getFieldCounterHTML('review'); ?></p>
<?php foreach($custom_fields as $field) { ?>
    <?php echo $form->getFieldLabel('custom_'.$field['id']); ?><?php echo $form->getFieldHTML('custom_'.$field['id']); ?>
    <?php if($form->hasFieldNote('custom_'.$field['id'])) { ?>
        <p class="note"><?php echo $form->getFieldNote('custom_'.$field['id']); ?></p>
    <?php } ?>
<?php } ?>
<?php if($form->fieldExists('security_code')) { ?>
    <?php echo $form->getFieldLabel('security_code'); ?><?php echo $form->getFieldHTML('security_code'); ?>
<?php } ?>
<?php echo $form->getFieldHTML('submit'); ?>
<?php echo $form->getFormCloseHTML(); ?>