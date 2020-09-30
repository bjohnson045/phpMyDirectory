<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('disable_billing'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('disable_billing'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_logo'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_logo'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_company'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_company'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_address'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_address'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_email_pdf'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_email_pdf'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_generation_days'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_generation_days'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Reminders</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_reminder_days'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_reminder_days'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_overdue_days_1'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_overdue_days_1'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_overdue_days_2'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_overdue_days_2'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('invoice_overdue_days_3'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('invoice_overdue_days_3'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Tax</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('tax_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('tax_type'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('compound_tax'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('compound_tax'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>