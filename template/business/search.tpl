<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('advanced_search'); ?></legend>
        <div class="form-group">
            <?php echo $form->getFieldLabel('keyword'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('keyword'); ?>
            </div>
        </div>
        <?php if(!$form->isFieldHidden('category') AND $form->fieldExists('category')) { ?>
        <div class="form-group">
            <?php echo $form->getFieldLabel('category'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('category'); ?>
            </div>
        </div>
        <?php } ?>
        <?php if(!$form->isFieldHidden('location_id') AND $form->fieldExists('location_id')) { ?>
        <div class="form-group">
            <?php echo $form->getFieldLabel('location_id'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('location_id'); ?>
            </div>
        </div>
        <?php } ?>
        <?php if(!$form->isFieldHidden('location') AND $form->fieldExists('location')) { ?>
        <div class="form-group">
            <?php echo $form->getFieldLabel('location'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('location'); ?>
            </div>
        </div>
        <?php } ?>
        <div class="form-group">
            <?php echo $form->getFieldLabel('zip'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('zip'); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->getFieldLabel('zip_miles'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('zip_miles'); ?>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <?php echo $form->getFieldHTML('submit'); ?>
        </div>
    </div>
    <?php echo $form->getFormCloseHTML(); ?>
</div>
<div class="form-container">
    <?php echo $form_products->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form_products->getFieldSetLabel('classifieds_search'); ?></legend>
        <div class="form-group">
            <?php echo $form_products->getFieldLabel('keyword'); ?>
            <div class="col-lg-10">
                <?php echo $form_products->getFieldHTML('keyword'); ?>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <?php echo $form_products->getFieldHTML('submit'); ?>
        </div>
    </div>
    <?php echo $form_products->getFormCloseHTML(); ?>
</div>
<div class="form-container">
    <?php echo $form_documents->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form_documents->getFieldSetLabel('documents_search'); ?></legend>
        <div class="form-group">
            <?php echo $form_documents->getFieldLabel('keyword'); ?>
            <div class="col-lg-10">
                <?php echo $form_documents->getFieldHTML('keyword'); ?>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <?php echo $form_documents->getFieldHTML('submit'); ?>
        </div>
    </div>
    <?php echo $form_documents->getFormCloseHTML(); ?>
</div>
<div class="form-container">
    <?php echo $form_images->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form_images->getFieldSetLabel('images_search'); ?></legend>
        <div class="form-group">
            <?php echo $form_images->getFieldLabel('keyword'); ?>
            <div class="col-lg-10">
                <?php echo $form_images->getFieldHTML('keyword'); ?>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <?php echo $form_images->getFieldHTML('submit'); ?>
        </div>
    </div>
    <?php echo $form_images->getFormCloseHTML(); ?>
</div>