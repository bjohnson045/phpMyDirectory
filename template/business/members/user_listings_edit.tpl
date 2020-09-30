<?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php if($listing['friendly_url_allow']) { ?>
                <?php echo $form->getFieldGroup('friendly_url'); ?>
            <?php } ?>
            <?php if($listing['logo_allow']) { ?>
                <?php echo $form->getFieldGroup('logo'); ?>
                <?php if($form->fieldExists('preview')) { ?>
                    <?php echo $form->getFieldGroup('preview'); ?>
                <?php } ?>
                <?php if($form->fieldExists('delete_logo')) { ?>
                    <?php echo $form->getFieldGroup('delete_logo'); ?>
                <?php } ?>
            <?php } ?>
            <?php if($listing['logo_background_allow']) { ?>
                <?php echo $form->getFieldGroup('logo_background'); ?>
                <?php if($form->fieldExists('logo_background_preview')) { ?>
                    <?php echo $form->getFieldGroup('logo_background_preview'); ?>
                <?php } ?>
                <?php if($form->fieldExists('logo_background_delete')) { ?>
                    <?php echo $form->getFieldGroup('logo_background_delete'); ?>
                <?php } ?>
            <?php } ?>
            <?php if(!$form->isFieldHidden('primary_category_id') AND $form->fieldExists('primary_category_id')) { ?>
                <?php echo $form->getFieldGroup('primary_category_id'); ?>
            <?php } ?>
            <?php if(!$form->isFieldHidden('categories') AND $form->fieldExists('categories')) { ?>
                <?php echo $form->getFieldGroup('categories'); ?>
            <?php } ?>
            <?php if($listing['short_description_size']) { ?>
                <?php echo $form->getFieldGroup('description_short'); ?>
            <?php } ?>
            <?php if($listing['description_size']) { ?>
                <?php echo $form->getFieldGroup('description'); ?>
            <?php } ?>
            <?php if($listing['keywords_limit']) { ?>
                <?php echo $form->getFieldGroup('keywords'); ?>
            <?php } ?>
            <?php if($listing['meta_title_size']) { ?>
                <?php echo $form->getFieldGroup('meta_title'); ?>
            <?php } ?>
            <?php if($listing['meta_description_size']) { ?>
                <?php echo $form->getFieldGroup('meta_description'); ?>
            <?php } ?>
            <?php if($listing['meta_keywords_limit']) { ?>
                <?php echo $form->getFieldGroup('meta_keywords'); ?>
            <?php } ?>
            <?php if($listing['hours_allow']) { ?>
                <?php echo $form->getFieldGroup('hours'); ?>
            <?php } ?>
            <?php if($listing['phone_allow']) { ?>
                <?php echo $form->getFieldGroup('phone'); ?>
            <?php } ?>
            <?php if($listing['fax_allow']) { ?>
                <?php echo $form->getFieldGroup('fax'); ?>
            <?php } ?>
            <?php if($listing['address_allow']) { ?>
                <?php echo $form->getFieldGroup('listing_address1'); ?>
                <?php echo $form->getFieldGroup('listing_address2'); ?>
            <?php } ?>
            <?php if(!$form->isFieldHidden('location_id')) { ?>
                <?php echo $form->getFieldGroup('location_id'); ?>
            <?php } ?>
            <?php if($listing['location_text_1_allow']) { ?>
                <?php echo $form->getFieldGroup('location_text_1'); ?>
            <?php } ?>
            <?php if($listing['location_text_2_allow']) { ?>
                <?php echo $form->getFieldGroup('location_text_2'); ?>
            <?php } ?>
            <?php if($listing['location_text_3_allow']) { ?>
                <?php echo $form->getFieldGroup('location_text_3'); ?>
            <?php } ?>
            <?php if($listing['zip_allow']) { ?>
                <?php echo $form->getFieldGroup('listing_zip'); ?>
            <?php } ?>
            <?php if($listing['coordinates_allow']) { ?>
                <?php echo $form->getFieldGroup('latitude'); ?>
                <?php echo $form->getFieldGroup('longitude'); ?>
            <?php } ?>
            <?php if($listing['www_allow']) { ?>
                <?php echo $form->getFieldGroup('www'); ?>
            <?php } ?>
            <?php if($listing['email_allow']) { ?>
                <?php echo $form->getFieldGroup('mail'); ?>
            <?php } ?>
            <?php if($listing['social_links_allow']) { ?>
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
            <?php if($form->isFieldHidden('primary_category_id')) { ?><?php echo $form->getFieldHTML('primary_category_id'); ?><?php } ?>
            <?php if($form->isFieldHidden('categories')) { ?><?php echo $form->getFieldHTML('categories'); ?><?php } ?>
            <?php if($form->isFieldHidden('location_id')) { ?><?php echo $form->getFieldHTML('location_id'); ?><?php } ?>
    </fieldset>
    <?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>