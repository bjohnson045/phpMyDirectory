<small>
<?php if($general_variables) { ?>
    <strong><?php echo $lang['admin_email_templates_variables']; ?>:</strong><br />
    <?php foreach((array) $general_variables as $variable) { ?>
        *<?php echo $variable; ?>*<br />
    <?php } ?>
    <?php if(count($specific_variables)) { ?>
        <br /><strong>Template Specific Variables:</strong><br />
        <?php foreach($specific_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($review_variables)) { ?>
        <br /><strong>Review Variables:</strong><br />
        <?php foreach($review_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($event_variables)) { ?>
        <br /><strong>Event Variables:</strong><br />
        <?php foreach($event_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($user_variables)) { ?>
        <br /><strong>User Variables:</strong><br />
        <?php foreach($user_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($order_variables)) { ?>
        <br /><strong>Order Variables:</strong><br />
        <?php foreach($order_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($invoice_variables)) { ?>
        <br /><strong>Invoice Variables:</strong><br />
        <?php foreach($invoice_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($listing_variables)) { ?>
        <br /><strong><?php echo $lang['admin_email_templates_variables_listings']; ?>:</strong><br />
        <?php foreach($listing_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
    <?php if(count($classified_variables)) { ?>
        <br /><strong><?php echo $lang['admin_email_templates_variables_classifieds']; ?>:</strong><br />
        <?php foreach($classified_variables as $variable) { ?>
            *<?php echo $variable; ?>*<br />
        <?php } ?>
    <?php } ?>
<?php } ?>
</small>