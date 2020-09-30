<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend>Bank Information</legend>
        <?php echo $form->getFieldLabel('bank_id'); ?><?php echo $form->getFieldHTML('bank_id'); ?>
    </fieldset>
    <fieldset class="buttonrow">
        <?php echo $form->getFieldHTML('submit'); ?>
    </fieldset>
    <?php echo $form->getFieldSetHTML('hidden'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>