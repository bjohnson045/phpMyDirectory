<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_listing_reviews_review']; ?></h2>
<?php if($log_in_url) { ?>
    <p><?php echo $lang['public_listing_reviews_have_account'];?> <a href="<?php echo $log_in_url; ?>"><?php echo $lang['public_listing_reviews_login']; ?></a></p>
<?php } ?>
<?php echo $form->getFormOpenHTML(); ?>
    <?php echo $form->getFieldGroup('name'); ?>
    <?php echo $form->getFieldGroup('title'); ?>
    <?php echo $form->getFieldGroup('rating'); ?>
    <?php foreach($categories AS $category) { ?>
        <?php echo $form->getFieldGroup('category_'.$category['id']); ?>
    <?php } ?>
    <?php echo $form->getFieldGroup('review'); ?>
    <?php foreach($custom_fields as $field) { ?>
        <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
    <?php } ?>
    <?php echo $form->getFieldGroup('security_code'); ?>
    <?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>
