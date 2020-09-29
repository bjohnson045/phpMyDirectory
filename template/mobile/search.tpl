<div data-role="collapsible-set">
    <div data-role="collapsible">
        <h3><?php echo $form->getFieldSetLabel('advanced_search'); ?></h3>
        <?php echo $form->getFormOpenHTML(); ?>
        <?php echo $form->getFieldLabel('keyword'); ?><?php echo $form->getFieldHTML('keyword'); ?>
        <?php if(!$form->isFieldHidden('category') AND $form->fieldExists('category')) { ?>
            <?php echo $form->getFieldLabel('category'); ?><?php echo $form->getFieldHTML('category'); ?>
        <?php } ?>
        <?php if(!$form->isFieldHidden('location_id') AND $form->fieldExists('location_id')) { ?>
            <?php echo $form->getFieldLabel('location_id'); ?><?php echo $form->getFieldHTML('location_id'); ?>
        <?php } ?>
        <?php if(!$form->isFieldHidden('location') AND $form->fieldExists('location')) { ?>
            <?php echo $form->getFieldLabel('location'); ?><?php echo $form->getFieldHTML('location'); ?>
        <?php } ?>
        <?php echo $form->getFieldLabel('zip'); ?><?php echo $form->getFieldHTML('zip'); ?></li>
        <?php echo $form->getFieldLabel('zip_miles'); ?><?php echo $form->getFieldHTML('zip_miles'); ?>
        <?php echo $form->getFieldHTML('submit'); ?>
        <?php echo $form->getFormCloseHTML(); ?>
    </div>
    <div data-role="collapsible">
        <h3><?php echo $form_products->getFieldSetLabel('classifieds_search'); ?></h3>
        <?php echo $form_products->getFormOpenHTML(); ?>
        <?php echo $form_products->getFieldLabel('keyword'); ?><?php echo $form_products->getFieldHTML('keyword'); ?>
        <?php echo $form_products->getFieldHTML('submit'); ?>
        <?php echo $form_products->getFormCloseHTML(); ?>
    </div>
    <div data-role="collapsible">
        <h3><?php echo $form_documents->getFieldSetLabel('documents_search'); ?></h3>
        <?php echo $form_documents->getFormOpenHTML(); ?>
        <?php echo $form_documents->getFieldLabel('keyword'); ?><?php echo $form_documents->getFieldHTML('keyword'); ?>
        <?php echo $form_documents->getFieldHTML('submit'); ?>
        <?php echo $form_documents->getFormCloseHTML(); ?>
    </div>
    <div data-role="collapsible">
        <h3><?php echo $form_images->getFieldSetLabel('images_search'); ?></h3>
        <?php echo $form_images->getFormOpenHTML(); ?>
        <?php echo $form_images->getFieldLabel('keyword'); ?><?php echo $form_images->getFieldHTML('keyword'); ?>
        <?php echo $form_images->getFieldHTML('submit'); ?>
        <?php echo $form_images->getFormCloseHTML(); ?>
    </div>
</div>