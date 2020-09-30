<script>
$(document).ready(function (){
    $('#classified_email_button').magnificPopup({
        type:'inline',
    });
    <?php if($email_form_show) { ?>
        $('#classified_email_button').trigger('click');
    <?php } ?>
});
</script>
<div itemscope itemtype="http://schema.org/Product">
    <h1><span itemprop="name"><?php echo $this->escape($title); ?></span></h1>
    <?php if($listing_title) { ?>
        <p><?php echo $lang['public_classified_from']; ?> <a href="<?php echo $listing_url; ?>"><?php echo $this->escape($listing_title); ?></a></p>
    <?php } ?>
    <div class="row">
        <div class="col-md-12">
            <?php echo $share; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <h2><?php echo $lang['public_classified_overview']; ?></h2>
            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                <?php if($price) { ?>
                    <p><strong><?php echo $lang['public_classified_price']; ?>:</strong> <span itemprop="price"><?php echo $this->escape($price); ?></span></p>
                <?php } ?>
                <p><strong><?php echo $lang['public_classified_date']; ?>:</strong> <span itemprop="validFrom" content="<?php echo $this->escape($date_iso); ?>"><?php echo $this->escape($date); ?></span></p>
                <?php if($expire_date) { ?>
                    <p><strong><?php echo $lang['public_classified_expire_date']; ?>:</strong> <span itemprop="validThrough" content="<?php echo $this->escape($expire_date_iso); ?>"><?php echo $this->escape($expire_date); ?></span></p>
                <?php } ?>
            </div>
            <?php echo $custom_fields; ?>
        </div>
        <div class="col-lg-6 col-md-6">
            <h2><?php echo $lang['public_classified_options']; ?></h2>
            <?php if($www) { ?>
                <span itemprop="sameAs"><a class="btn btn-default btn-lg" target="_blank" rel="nofollow" href="<?php echo $this->escape($www); ?>"><?php echo $lang['public_classified_view']; ?></a></span>
            <?php } ?>
            <?php if($buy_url) { ?>
                <a class="btn btn-default btn-lg btn-success" target="_blank" rel="nofollow" href="<?php echo $this->escape($buy_url); ?>"><?php echo $lang['public_classified_buy']; ?></a><br />
            <?php } ?>
            <?php if($print) { ?>
                <a class="btn btn-default btn-lg" rel="nofollow" href="<?php echo $print; ?>"> <?php echo $lang['public_classified_print']; ?></a>
            <?php } ?>
            <?php if($pdf) { ?>
                <a class="btn btn-default btn-lg" rel="nofollow" href="<?php echo $pdf; ?>"> <?php echo $lang['public_classified_pdf_export']; ?></a>
            <?php } ?>
            <a id="classified_email_button" class="btn btn-default btn-lg" rel="nofollow" href="#classified_email_popup"> <?php echo $lang['public_classified_email']; ?></a>
        </div>
    </div>
    <?php if($description) { ?>
        <h2><?php echo $lang['public_classified_description']; ?></h2>
        <span itemprop="description"><?php echo $this->escape_html($description); ?></span>
    <?php } ?>
    <?php if($other_classifieds) { ?>
        <h2><?php echo $lang['public_classified_other']; ?> <?php echo $this->escape($listing_title); ?></h2>
        <?php foreach($other_classifieds AS $classified) { ?>
            <p><a href="<?php echo $classified['url']; ?>"><?php echo $this->escape($classified['title']); ?></a></p>
        <?php } ?>
    <?php } ?>
    <?php if(isset($classified_images) AND !empty($classified_images)) { ?>
        <h2><?php echo $lang['public_classified_images']; ?></h2>
        <?php foreach($classified_images AS $image_key=>$image) { ?>
            <a rel="image_group1" title="<?php echo $this->escape($title); ?>" class="image_group" href="<?php echo $image['image_url']; ?>">
                <img <?php if($image_key == 0) { ?>itemprop="image" alt="<?php echo $this->escape($title); ?>"<?php }?>class="thumbnail" src="<?php echo $image['thumbnail_url']; ?>" />
            </a>
        <?php } ?>
    <?php } ?>
    <div id="classified_email_popup" class="white-popup mfp-hide">
        <h2 id="classified_email"><?php echo $lang['public_classified_email']; ?></h2>
        <?php echo $message; ?>
        <?php echo $email_form->getFormOpenHTML(); ?>
            <?php echo $email_form->getFieldGroup('from_name'); ?>
            <?php echo $email_form->getFieldGroup('from_email'); ?>
            <?php echo $email_form->getFieldGroup('message'); ?>
            <?php foreach($email_form_fields as $field) { ?>
                <?php echo $email_form->getFieldGroup('custom_'.$field['id']); ?>
            <?php } ?>
            <?php echo $email_form->getFieldGroup('copy'); ?>
            <?php if($email_form->fieldExists('security_code')) { ?>
                <?php echo $email_form->getFieldGroup('security_code'); ?>
            <?php } ?>
            <?php echo $email_form->getFormActions(); ?>
        <?php echo $email_form->getFormCloseHTML(); ?>
    </div>
</div>