<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_memberships','admin_products','admin_products_pricing'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_products_view');

$products = $PMDR->get('Products');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_products_delete');
    if(!$db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS_PRICING." WHERE product_id=?",array($_GET['id']))) {
        $products->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_products'))),'delete');
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_products'));
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('name',$PMDR->getLanguage('admin_products_name'));
    $table_list->addColumn('type',$PMDR->getLanguage('admin_products_type'));
    $table_list->addColumn('hidden',$PMDR->getLanguage('admin_products_hidden'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_products_order').' [<a href="" onclick="updateOrdering(\''.T_PRODUCTS.'\',\'table_list_form\'); return false;">'.$PMDR->getLanguage('admin_update').'</a>]');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS));
    $records = $db->GetAll("SELECT p.*, COUNT(pp.id) AS pricing_id_count FROM ".T_PRODUCTS." p LEFT JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id GROUP BY p.id ORDER BY ordering LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        $records[$key]['type'] = $PMDR->getLanguage('admin_products_types_'.$record['type']);
        $records[$key]['ordering'] = '<input id="ordering_'.$record['id'].'" class="form-control" style="width: 45px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $records[$key]['hidden'] = $PMDR->get('HTML')->icon($record['hidden']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('duplicate',array('label'=>'Copy','href'=>URL.'?action=add&id='.$record['id'],'onclick'=>'return confirm(\''.$PMDR->getLanguage('messages_confirm').'\');'));
        if(!$record['pricing_id_count']) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    if(!$db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS_GROUPS)) {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_products_add_group_error'));
        redirect(BASE_URL_ADMIN.'/admin_products_groups.php?action=add');
    }
    $fields = $db->GetCol("SELECT id FROM ".T_FIELDS);
    $PMDR->get('Authentication')->checkPermission('admin_products_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('product_details',array('legend'=>$PMDR->getLanguage('admin_products_product')));
    $form->addField('group_id','select',array('label'=>$PMDR->getLanguage('admin_products_group'),'fieldset'=>'product_details','options'=>$db->GetAssoc("SELECT id, name FROM ".T_PRODUCTS_GROUPS),'help'=>$PMDR->getLanguage('admin_products_help_group_id')));
    if(isset($_GET['group_id'])) {
        $form->setFieldAttribute('group_id','value',$_GET['group_id']);
    }
    $form->addField('type','hidden',array('label'=>$PMDR->getLanguage('admin_products_type'),'fieldset'=>'product_details','value'=>'listing_membership'));
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_products_name'),'fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_name')));
    $form->addField('active','checkbox',array('label'=>$PMDR->getLanguage('admin_products_active'),'value'=>1,'fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_active')));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_products_description'),'fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_description')));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_products_order'),'value'=>'0','fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_ordering')));
    $form->addField('suspend_overdue_days','text',array('label'=>$PMDR->getLanguage('admin_products_suspend_overdue_days'),'value'=>'0','fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_suspend_overdue_days')));
    $form->addField('hidden','checkbox',array('label'=>$PMDR->getLanguage('admin_products_hidden'),'fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_hidden')));
    $form->addField('taxed','checkbox',array('label'=>$PMDR->getLanguage('admin_products_taxed'),'fieldset'=>'product_details','help'=>$PMDR->getLanguage('admin_products_help_taxed')));
    $form->addField('upgrades','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_products_upgrades'),'fieldset'=>'product_details','value'=>'','options'=>array('type'=>'products_tree','product_type'=>'listing_membership','hidden'=>true),'help'=>$PMDR->getLanguage('admin_products_help_upgrades')));
    $form->addFieldSet('permissions',array('legend'=>$PMDR->getLanguage('admin_memberships_permissions')));
    $form->addField('priority','text',array('label'=>$PMDR->getLanguage('admin_memberships_priority'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_priority'),'value'=>0));
    $form->addField('header_template_file','text',array('label'=>$PMDR->getLanguage('admin_memberships_header_template_file'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_header_template_file')));
    $form->addField('footer_template_file','text',array('label'=>$PMDR->getLanguage('admin_memberships_footer_template_file'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_footer_template_file')));
    $form->addField('wrapper_template_file','text',array('label'=>$PMDR->getLanguage('admin_memberships_wrapper_template_file'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_wrapper_template_file')));
    $form->addField('template_file','text',array('label'=>$PMDR->getLanguage('admin_memberships_template_file'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_template_file')));
    $form->addField('template_file_results','text',array('label'=>$PMDR->getLanguage('admin_memberships_template_file_results'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_template_file_results')));
    $form->addField('category_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_category_limit'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_category_limit')));
    $form->addField('categories','tree_select_expanding_checkbox',array('fieldset'=>'permissions','options'=>array('type'=>'category_tree','search'=>true,'select_mode'=>3,'bypass_setup'=>true)));
    $form->addField('featured','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_featured'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_featured')));
    $form->addField('friendly_url_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_friendly_url_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_friendly_url_allow')));
    $form->addField('html_editor_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_html_editor_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_html_editor_allow')));
    $form->addField('phone_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_phone_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_phone_allow')));
    $form->addField('fax_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_fax_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_fax_allow')));
    $form->addField('address_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_address_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_address_allow')));
    $form->addField('zip_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_zip_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_zip_allow')));
    $form->addField('hours_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_hours_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_hours_allow')));
    $form->addField('coordinates_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_coordinates_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_coordinates_allow')));
    $form->addField('email_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_email_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_email_allow')));
    $form->addField('email_friend_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_email_friend_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_email_friend_allow')));
    $form->addField('www_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_website_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_website_allow')));
    if(ADDON_LINK_CHECKER) {
        $form->addField('require_reciprocal','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_require_reciprocal'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_require_reciprocal')));
    }
    $form->addField('www_screenshot_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_www_screenshot_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_www_screenshot_allow')));
    $form->addField('map_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_map_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_map_allow')));
    $form->addField('logo_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_logo_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_logo_allow')));
    $form->addField('logo_background_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_logo_background_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_logo_background_allow')));
    $form->addField('reviews_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_reviews_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_reviews_allow')));
    $form->addField('ratings_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_ratings_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_ratings_allow')));
    $form->addField('suggestion_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_suggestions_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_suggestion_allow')));
    $form->addField('claim_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_claim_allow'),'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_claim_allow')));
    $form->addField('pdf_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_pdf_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_pdf_allow')));
    $form->addField('addtofavorites_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_addtofavorites_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_addtofavorites_allow')));
    $form->addField('vcard_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_vcard_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_vcard_allow')));
    $form->addField('print_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_print_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_print_allow')));
    $form->addField('qrcode_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_qrcode_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_qrcode_allow')));
    $form->addField('share_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_share_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_share_allow')));
    $form->addField('social_links_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_social_links_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_social_links_allow')));
    $form->addField('contact_requests_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_contact_requests_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_contact_requests_allow')));
    $form->addField('classifieds_images_allow','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_classified_images_allow'),'fieldset'=>'permissions','value'=>'1','help'=>$PMDR->getLanguage('admin_memberships_help_classified_images_allow')));
    $form->addField('classifieds_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_classified_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_classified_limit')));
    $form->addField('events_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_event_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_event_limit')));
    $form->addField('blog_posts_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_blog_post_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_blog_post_limit')));
    $form->addField('jobs_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_jobs_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_jobs_limit')));
    $form->addField('images_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_images_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_images_limit')));
    $form->addField('documents_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_documents_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_documents_limit')));
    $form->addField('locations_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_locations_limit'),'fieldset'=>'permissions','value'=>'5','help'=>$PMDR->getLanguage('admin_memberships_help_locations_limit')));
    $form->addField('title_size','text',array('label'=>$PMDR->getLanguage('admin_memberships_title_size'),'fieldset'=>'permissions','value'=>'200','help'=>$PMDR->getLanguage('admin_memberships_help_title_size')));
    $form->addField('description_size','text',array('label'=>$PMDR->getLanguage('admin_memberships_description_size'),'fieldset'=>'permissions','value'=>'500','help'=>$PMDR->getLanguage('admin_memberships_help_description_size')));
    $form->addField('description_images_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_description_images_limit'),'fieldset'=>'permissions','value'=>'3','help'=>$PMDR->getLanguage('admin_memberships_help_description_images_limit')));
    $form->addField('short_description_size','text',array('label'=>$PMDR->getLanguage('admin_memberships_short_description_size'),'fieldset'=>'permissions','value'=>'100','help'=>$PMDR->getLanguage('admin_memberships_help_short_description_size')));
    $form->addField('keywords_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_keyword_limit'),'fieldset'=>'permissions','value'=>'10','help'=>$PMDR->getLanguage('admin_memberships_help_keyword_limit')));
    $form->addField('meta_title_size','text',array('label'=>$PMDR->getLanguage('admin_memberships_meta_title_size'),'fieldset'=>'permissions','value'=>'100','help'=>$PMDR->getLanguage('admin_memberships_help_meta_title_size')));
    $form->addField('meta_description_size','text',array('label'=>$PMDR->getLanguage('admin_memberships_meta_description_size'),'fieldset'=>'permissions','value'=>'150','help'=>$PMDR->getLanguage('admin_memberships_help_meta_description_size')));
    $form->addField('meta_keywords_limit','text',array('label'=>$PMDR->getLanguage('admin_memberships_meta_keywords_limit'),'fieldset'=>'permissions','value'=>'10','help'=>$PMDR->getLanguage('admin_memberships_help_meta_keywords_limit')));
    $banners = $db->GetAll("SELECT * FROM ".T_BANNER_TYPES);
    foreach($banners as $banner) {
        $form->addField('banner_limit_'.$banner['id'],'text',array('label'=>$banner['name'],'fieldset'=>'permissions','help'=>$PMDR->getLanguage('admin_memberships_help_banner_types')));
    }
    if(count($fields)) {
        $form->addField('custom_fields','tree_select_expanding_checkbox',array('label'=>'Custom Fields','fieldset'=>'details','options'=>array('type'=>'custom_fields')));
    }
    if($_GET['action'] == 'edit') {
        $form->addFieldSet('options',array('legend'=>$PMDR->getLanguage('admin_memberships_options')));
        $form->addField('update_current','checkbox',array('label'=>$PMDR->getLanguage('admin_memberships_update'),'fieldset'=>'options','help'=>$PMDR->getLanguage('admin_memberships_help_update_current')));
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_products_edit'));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_products_add'));
    }

    if(isset($_GET['id'])) {
        $product = $db->GetRow("SELECT p.*, m.id AS membership_id, m.* FROM ".T_PRODUCTS." p INNER JOIN ".T_MEMBERSHIPS." m ON p.type_id=m.id WHERE p.id=? AND p.type='listing_membership'",array($_GET['id']));
        $product['upgrades'] = explode(',',$product['upgrades']);
        $product['categories'] = explode(',',$product['categories']);
        $product['custom_fields'] = array();
        foreach($fields AS $field_id) {
            if(isset($product['custom_'.$field_id.'_allow']) AND $product['custom_'.$field_id.'_allow']) {
                $product['custom_fields'][] = $field_id;
            }
        }
        $form->loadValues($product);
    }

    $form->addValidator('name',new Validate_NonEmpty());
    $form->addValidator('priority',new Validate_NonEmpty());
    $form->addValidator('ordering',new Validate_NonEmpty());
    $form->addValidator('suspend_overdue_days',new Validate_NonEmpty());

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(is_array($fields)) {
            foreach($fields AS $field_id) {
                $data['custom_'.$field_id.'_allow'] = in_array($field_id,$data['custom_fields']);
            }
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $data['upgrades'] = implode(',',$data['upgrades']);
            $data['categories'] = implode(',',$data['categories']);
            $data['type_id'] = $product['membership_id'];

            if($data['description_images_limit']) {
                $allowed_tags = $PMDR->get('HTML')->tagsToArray($PMDR->getConfig('allowed_html_tags'));
                if(!isset($allowed_tags['img'])) {
                    $allowed_tags['img'] = array('src');
                    $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname=?",array($PMDR->get('HTML')->tagsToString($allowed_tags),'allowed_html_tags'));
                }
            }

            if($_GET['action']=='add') {
                $product_id = $products->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_products'))),'insert');
                $PMDR->addMessage('warning','Please add at least one pricing option for the product that was just created.  Use a price of 0.00 to create a free pricing option.');
                redirect_url(BASE_URL_ADMIN.'/admin_products_pricing.php?action=add&product_id='.$product_id);
            } elseif($_GET['action'] == 'edit') {
                $products->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_products'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_products_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>