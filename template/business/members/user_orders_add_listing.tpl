<?php if($form->fieldExists('submit_primary')) { ?>
    <?php echo $form->getFormOpenHTML(); ?>
    <?php echo $form->getFieldGroup('primary_category_id'); ?>
    <?php echo $form->getFormActions(); ?>
    <?php echo $form->getFormCloseHTML(); ?>
<?php } else { ?>
    <?php echo $form->getFormOpenHTML(); ?>
        <?php echo $form->getFieldGroup('title'); ?>
        <?php echo $form->getFieldGroup('friendly_url'); ?>
        <?php if($product['logo_allow']) { ?>
            <?php echo $form->getFieldGroup('logo'); ?>
        <?php } ?>
        <?php if($product['logo_background_allow']) { ?>
            <?php echo $form->getFieldGroup('logo_background'); ?>
        <?php } ?>
        <?php if(!$form->isFieldHidden('categories') AND $form->fieldExists('categories')) { ?>
            <?php echo $form->getFieldGroup('categories'); ?>
        <?php } ?>
        <?php if($product['short_description_size']) { ?>
            <?php echo $form->getFieldGroup('description_short'); ?>
        <?php } ?>
        <?php if($product['description_size']) { ?>
            <?php echo $form->getFieldGroup('description'); ?>
        <?php } ?>
        <?php if($product['keywords_limit']) { ?>
            <?php echo $form->getFieldGroup('keywords'); ?>
        <?php } ?>
        <?php if($product['meta_title_size']) { ?>
            <?php echo $form->getFieldGroup('meta_title'); ?>
        <?php } ?>
        <?php if($product['meta_description_size']) { ?>
            <?php echo $form->getFieldGroup('meta_description'); ?>
        <?php } ?>
        <?php if($product['meta_keywords_limit']) { ?>
            <?php echo $form->getFieldGroup('meta_keywords'); ?>
        <?php } ?>
        <?php if($product['hours_allow']) { ?>
            <?php echo $form->getFieldGroup('hours'); ?>
        <?php } ?>
        <?php if($product['phone_allow']) { ?>
            <?php echo $form->getFieldGroup('phone'); ?>
        <?php } ?>
        <?php if($product['fax_allow']) { ?>
            <?php echo $form->getFieldGroup('fax'); ?>
        <?php } ?>
        <?php if($product['address_allow']) { ?>
            <?php echo $form->getFieldGroup('listing_address1'); ?>
            <?php echo $form->getFieldGroup('listing_address2'); ?>
        <?php } ?>
        <?php if(!$form->isFieldHidden('location_id')) { ?>
            <?php echo $form->getFieldGroup('location_id'); ?>
        <?php } ?>
        <?php if($product['location_text_1']) { ?>
            <?php echo $form->getFieldGroup('location_text_1'); ?>
        <?php } ?>
        <?php if($product['location_text_2']) { ?>
            <?php echo $form->getFieldGroup('location_text_2'); ?>
        <?php } ?>
        <?php if($product['location_text_3']) { ?>
            <?php echo $form->getFieldGroup('location_text_3'); ?>
        <?php } ?>
        <?php if($product['zip_allow']) { ?>
            <?php echo $form->getFieldGroup('listing_zip'); ?>
        <?php } ?>
        <?php if($product['coordinates_allow']) { ?>
            <?php echo $form->getFieldGroup('latitude'); ?>
            <?php echo $form->getFieldGroup('longitude'); ?>
        <?php } ?>
        <?php if($product['www_allow']) { ?>
            <?php echo $form->getFieldGroup('www'); ?>
        <?php } ?>
        <?php if($product['email_allow']) { ?>
            <?php echo $form->getFieldGroup('mail'); ?>
        <?php } ?>
        <?php if($product['social_links_allow']) { ?>
            <?php echo $form->getFieldGroup('facebook_page_id'); ?>
            <?php echo $form->getFieldGroup('twitter_id'); ?>
            <?php echo $form->getFieldGroup('google_page_id'); ?>
            <?php echo $form->getFieldGroup('linkedin_id'); ?>
            <?php echo $form->getFieldGroup('linkedin_company_id'); ?>
            <?php echo $form->getFieldGroup('pinterest_id'); ?>
            <?php echo $form->getFieldGroup('youtube_id'); ?>
            <?php echo $form->getFieldGroup('foursquare_id'); ?>
            <?php echo $form->getFieldGroup('instagram_id'); ?>
        <?php } ?>
        <?php foreach($fields as $field) { ?>
            <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
        <?php } ?>
        <?php if($product['images_limit']) { ?>
            <?php for($x=1; $x <= min($product['images_limit'],10); $x++) { ?>
                <?php echo $form->getFieldGroup('image'.$x); ?>
            <?php } ?>
        <?php } ?>
    <?php echo $form->getFieldHTML('primary_category_id'); ?>
    <?php if($form->isFieldHidden('location_id')) { ?><?php echo $form->getFieldHTML('location_id'); ?><?php } ?>
    <?php if($form->isFieldHidden('categories')) { ?><?php echo $form->getFieldHTML('categories'); ?><?php } ?>
    <?php echo $form->getFormActions('submit'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
<?php } ?>