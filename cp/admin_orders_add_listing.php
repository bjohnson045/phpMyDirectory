<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_orders','admin_listings','general_locations','email_templates','admin_users'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_orders_edit');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_orders_add.tpl');
if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('orders'));
}
$template_content->set('title',$PMDR->getLanguage('admin_orders_add').' - '.$PMDR->getLanguage('admin_listings_listing'));

if(isset($_GET['order_id'])) {
    if(!$order = $db->GetRow("SELECT * FROM ".T_ORDERS." WHERE id=?",array($_GET['order_id']))) {
        redirect();
    } else {
        $user = $PMDR->get('Users')->getRow($order['user_id']);
        $product = $PMDR->get('Products')->getByPricingID($order['pricing_id'],$user['id']);
    }
} else {
    if(!$user = $PMDR->get('Users')->getRow($_GET['user_id'])) {
        redirect(BASE_URL_ADMIN.'/admin_orders_add.php');
    }
    if(!$product = $PMDR->get('Products')->getByPricingID($_GET['pricing_id'],$user['id'])) {
        redirect(BASE_URL_ADMIN.'/admin_orders_add.php');
    }
}

$category_count = $PMDR->get('Categories')->getCount();
$location_count = $PMDR->get('Locations')->getCount();

if(!isset($_GET['primary_category_id'])) {
    if($category_count > 1) {
        $form = $PMDR->getNew('Form');
        $form->addFieldSet('listing',array('legend'=>$PMDR->getLanguage('admin_listings_primary_category')));
        if($PMDR->getConfig('category_select_type') == 'tree_select') {
            $form->addField('primary_category_id','tree_select',array('label'=>$PMDR->getLanguage('admin_listings_primary_category'),'fieldset'=>'listing','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect()));
        } elseif($PMDR->getConfig('category_select_type') == 'tree_select_cascading') {
            $form->addField('primary_category_id','tree_select_cascading',array('label'=>$PMDR->getLanguage('admin_listings_primary_category'),'fieldset'=>'listing','value'=>'','options'=>array('type'=>'category_tree','bypass_setup'=>true,'search'=>true)));
        } else {
            $form->addField('primary_category_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_listings_primary_category'),'fieldset'=>'listing','value'=>'','options'=>array('type'=>'category_tree','bypass_setup'=>true,'search'=>true)));
        }
    } elseif($category_count == 1) {
        redirect(array('user_id'=>$_GET['user_id'],'create_invoice'=>$_GET['create_invoice'],'pricing_id'=>$product['pricing_id'],'primary_category_id'=>$PMDR->get('Categories')->getOneID()));
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_listings_add_category_error'));
        redirect(BASE_URL_ADMIN.'/admin_orders_add.php');
    }

    $form->addValidator('primary_category_id',new Validate_NonEmpty());
    $form->addField('submit_primary','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
} else {
    if(!$db->GetRow("SELECT id FROM ".T_CATEGORIES." WHERE id=?",array($_GET['primary_category_id']))) {
        redirect(array('pricing_id'=>$product['pricing_id']));
    }

    /** @var Form */
    $form = $PMDR->getNew('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('listing',array('legend'=>$PMDR->getLanguage('admin_listings_listing')));
    $form->addField('date','datetime',array('label'=>'Date','fieldset'=>'listing','value'=>$PMDR->get('Dates')->dateTimeNow()));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_listings_title'),'fieldset'=>'listing','counter'=>$product['title_size'],'onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    $form->addField('friendly_url','text',array('label'=>$PMDR->getLanguage('admin_listings_friendly_url'),'fieldset'=>'listing'));

    if($product['logo_allow']) {
        $form->addField('logo','file',array('label'=>$PMDR->getLanguage('admin_listings_logo'),'fieldset'=>'listing','options'=>array('url_allow'=>true)));
        $form->addValidator('logo',new Validate_Image($PMDR->getConfig('image_logo_width'),$PMDR->getConfig('image_logo_height'),$PMDR->getConfig('image_logo_size'),explode(',',$PMDR->getConfig('logos_formats'))));
        $form->addFieldNote('logo',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('image_logo_size')));
    }

    if($product['logo_background_allow']) {
        $form->addField('logo_background','file',array('label'=>$PMDR->getLanguage('admin_listings_logo_background'),'fieldset'=>'listing','options'=>array('url_allow'=>true)));
        $form->addValidator('logo_background',new Validate_Image($PMDR->getConfig('logo_background_width'),$PMDR->getConfig('logo_background_height'),$PMDR->getConfig('logo_background_size'),explode(',',$PMDR->getConfig('logos_formats'))));
        $form->addFieldNote('logo_background',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('image_logo_background_size')));
    }

    if($category_count > 1 AND $product['category_limit'] > 1) {
        if($PMDR->getConfig('category_select_type') == 'tree_select' OR $PMDR->getConfig('category_select_type') == 'tree_select_multiple') {
            if($product['category_limit'] == 2) {
                $form->addField('categories','tree_select',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect()));
            } else {
                $form->addField('categories','tree_select_multiple',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','limit'=>$product['category_limit'],'options'=>$PMDR->get('Categories')->getSelect()));
            }
        } elseif($PMDR->getConfig('category_select_type') == 'tree_select_cascading' OR $PMDR->getConfig('category_select_type') == 'tree_select_cascading_multiple') {
            if($product['category_limit'] == 2) {
                $form->addField('categories','tree_select_cascading',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','limit'=>$product['category_limit'],'search'=>true)));
            } else {
                $form->addField('categories','tree_select_cascading_multiple',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','limit'=>$product['category_limit'],'search'=>true)));
            }
        } else {
            if($product['category_limit'] == 2) {
                $form->addField('categories','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','search'=>true)));
            } else {
                $form->addField('categories','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','limit'=>$product['category_limit'],'options'=>array('type'=>'category_tree','search'=>true)));
            }
        }
    }
    $form->addField('primary_category_id','hidden',array('label'=>$PMDR->getLanguage('admin_listings_categories'),'fieldset'=>'listing','value'=>$_GET['primary_category_id']));

    if($product['short_description_size']) {
        $form->addField('description_short','textarea',array('label'=>$PMDR->getLanguage('admin_listings_short_description'),'fieldset'=>'listing','counter'=>$product['short_description_size']));
    }
    if($product['description_size']) {
        if($product['html_editor_allow']) {
            $form->addField('description','htmleditor',array('label'=>$PMDR->getLanguage('admin_listings_description'),'fieldset'=>'listing','counter'=>$product['description_size']));
        } else {
            $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_listings_description'),'fieldset'=>'listing','counter'=>$product['description_size']));
        }
        $form->addValidator('description', new Validate_Length($product['description_size']));
    }
    if($product['keywords_limit']) {
        $form->addField('keywords','textarea',array('label'=>$PMDR->getLanguage('admin_listings_keywords'),'fieldset'=>'listing'));
        $form->addValidator('keywords',new Validate_Word_Count($product['keywords_limit']));
        $form->addFieldNote('keywords',$PMDR->getLanguage('admin_listings_limit').': '.$product['keywords_limit']);
    }
    if($product['meta_title_size']) {
        $form->addField('meta_title','text',array('label'=>$PMDR->getLanguage('admin_listings_meta_title'),'fieldset'=>'listing','counter'=>$product['meta_title_size']));
    }
    if($product['meta_description_size']) {
        $form->addField('meta_description','textarea',array('label'=>$PMDR->getLanguage('admin_listings_meta_description'),'fieldset'=>'listing','counter'=>$product['meta_description_size']));
    }
    if($product['meta_keywords_limit']) {
        $form->addField('meta_keywords','textarea',array('label'=>$PMDR->getLanguage('admin_listings_meta_keywords'),'fieldset'=>'listing'));
        $form->addValidator('meta_keywords',new Validate_Word_Count($product['meta_keywords_limit']));
        $form->addFieldNote('meta_keywords',$PMDR->getLanguage('admin_listings_limit').': '.$product['meta_keywords_limit']);
    }
    if($product['phone_allow']) {
        $form->addField('phone','text',array('label'=>$PMDR->getLanguage('admin_listings_phone'),'fieldset'=>'listing'));
    }
    if($product['fax_allow']) {
        $form->addField('fax','text',array('label'=>$PMDR->getLanguage('admin_listings_fax'),'fieldset'=>'listing'));
    }
    if($product['address_allow']) {
        $form->addField('listing_address1','text',array('label'=>$PMDR->getLanguage('admin_listings_address1'),'fieldset'=>'listing'));
        $form->addField('listing_address2','text',array('label'=>$PMDR->getLanguage('admin_listings_address2'),'fieldset'=>'listing'));
    }

    if($location_count > 1) {
        if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'listing','first_option'=>'','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 ORDER BY left_")));
        } else {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'listing','options'=>array('type'=>'location_tree','search'=>true)));
        }
        $form->addValidator('location_id',new Validate_NonEmpty());
    } else {
        $form->addField('location_id','hidden',array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'listing','value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
    }

    if($PMDR->getConfig('location_text_1')) {
        $product['location_text_1'] = true;
        $form->addField('location_text_1','text',array('label'=>$PMDR->getLanguage('general_locations_text_1'),'fieldset'=>'listing'));
    }
    if ($PMDR->getConfig('location_text_2')) {
        $product['location_text_2'] = true;
        $form->addField('location_text_2','text',array('label'=>$PMDR->getLanguage('general_locations_text_2'),'fieldset'=>'listing'));
    }
    if($PMDR->getConfig('location_text_3')) {
        $product['location_text_3'] = true;
        $form->addField('location_text_3','text',array('label'=>$PMDR->getLanguage('general_locations_text_3'),'fieldset'=>'listing'));
    }
    if($product['zip_allow']) {
        $form->addField('listing_zip','text',array('label'=>$PMDR->getLanguage('admin_listings_zip_code'),'fieldset'=>'listing'));
    }
    if($product['hours_allow']) {
        $form->addField('hours','hours',array('label'=>$PMDR->getLanguage('admin_listings_hours'),'fieldset'=>'listing','options'=>array('hours_24'=>true,'hours_24_label'=>$PMDR->getLanguage('admin_listings_hours_24'))));
    }
    if($product['coordinates_allow']) {
        $form->addField('latitude','text',array('label'=>$PMDR->getLanguage('admin_listings_latitude'),'fieldset'=>'listing'));
        $form->addField('longitude','text',array('label'=>$PMDR->getLanguage('admin_listings_longitude'),'fieldset'=>'listing'));
        $form->addPicker('longitude','coordinates',null,array('label'=>$PMDR->getLanguage('admin_listings_select_coordinates'),'coordinates'=>$PMDR->getConfig('map_select_coordinates'),'zoom'=>$PMDR->getConfig('map_select_zoom'),'marker'=>false));
    }
    if($product['www_allow']) {
        $form->addField('www','text',array('label'=>$PMDR->getLanguage('admin_listings_website'),'fieldset'=>'listing'));
        $form->addFieldNote('www','Example: http://www.domain.com');
        $form->addValidator('www',new Validate_URL(false));
    }
    if($product['email_allow']) {
        $form->addField('mail','text',array('label'=>$PMDR->getLanguage('admin_listings_email'),'fieldset'=>'listing'));
        $form->addValidator('mail',new Validate_Email(false));
    }
    if($product['social_links_allow']) {
        $form->addField('facebook_page_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_facebook_page_id'),'fieldset'=>'listing','prepend'=>'http://facebook.com/'));
        $form->addField('twitter_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_twitter_id'),'fieldset'=>'listing','prepend'=>'http://twitter.com/'));
        $form->addField('google_page_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_google_page_id'),'fieldset'=>'listing','prepend'=>'http://plus.google.com/'));
        $form->addField('linkedin_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_linkedin_id'),'fieldset'=>'listing','prepend'=>'http://linkedin.com/pub/'));
        $form->addField('linkedin_company_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_linkedin_company_id'),'fieldset'=>'listing','prepend'=>'http://linkedin.com/company/'));
        $form->addField('pinterest_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_pinterest_id'),'fieldset'=>'listing','prepend'=>'http://pinterest.com/'));
        $form->addField('youtube_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_youtube_id'),'fieldset'=>'listing','prepend'=>'http://youtube.com/user/'));
        $form->addField('foursquare_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_foursquare_id'),'fieldset'=>'listing','prepend'=>'http://foursquare.com/'));
        $form->addField('instagram_id','text_group',array('label'=>$PMDR->getLanguage('admin_listings_instagram_id'),'fieldset'=>'listing','prepend'=>'http://instagram.com/'));
    }

    $listing_fields = $PMDR->get('Fields')->addToForm($form,'listings',array('fieldset'=>'listing','filter'=>$product,'category'=>$_GET['primary_category_id']));

    if($product['images_limit'] > 0) {
        for($x=1; $x <= $product['images_limit']; $x++) {
            $form->addField('image'.$x,'file',array('label'=>$PMDR->getLanguage('admin_listings_images').' '.$x,'fieldset'=>'listing'));
            $form->addValidator('image'.$x,new Validate_Image($PMDR->getConfig('gallery_image_width'),$PMDR->getConfig('gallery_image_height'),$PMDR->getConfig('gallery_image_size'),explode(',',$PMDR->getConfig('images_formats'))));
        }
    }

    $form->addField('comment','textarea',array('label'=>$PMDR->getLanguage('admin_listings_comment'),'fieldset'=>'listing'));
    $form->addField('claimed','checkbox',array('label'=>$PMDR->getLanguage('admin_listings_mark_claimed'),'fieldset'=>'listing','value'=>1,'help'=>$PMDR->getLanguage('admin_listings_mark_claimed_help')));

    $form->addValidator('date',new Validate_DateTime(true));
    $form->addValidator('friendly_url',new Validate_Friendly_URL());
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if(isset($_GET['copy'])) {
        if($listing_copy = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($_GET['copy']))) {
            $listing_copy['categories'] = $db->GetCol("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=? AND cat_id!=?",array($listing_copy['id'],$listing_copy['primary_category_id']));
            $listing_copy['title'] .= ' (Copy)';
            if($logo_url = get_file_url(LOGO_PATH.$listing_copy['id'].'.'.$listing_copy['logo_extension'])) {
                $listing_copy['logo'] = $logo_url;
            }
            $form->loadValues($listing_copy);
            $PMDR->addMessage('notice','Information copied from listing "'.$listing_copy['title'].'" (ID: '.$listing_copy['id'].')');
        }
    }
}

if($form->wasSubmitted('submit_primary')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        if(isset($_GET['order_id'])) {
            redirect(null,array('create_invoice'=>0,'primary_category_id'=>$data['primary_category_id'],'order_id'=>$_GET['order_id']));
        } else {
            redirect(null,array('pricing_id'=>$product['pricing_id'],'create_invoice'=>$_GET['create_invoice'],'primary_category_id'=>$data['primary_category_id'],'user_id'=>$user['id']));
        }
    }
}

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$PMDR->getConfig('mod_rewrite_listings_id')) {
        if($data['friendly_url'] != '') {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE friendly_url=?",array($data['friendly_url']))) {
                $form->addError('The friendly URL entered is already in use.','friendly_url');
            }
        } else {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE friendly_url=?",array($data['friendly_url'] = Strings::rewrite($data['title'])))) {
                $form->addError('The title is currently in use by another listing.','title');
            }
        }
    } elseif($data['friendly_url'] == '') {
        $data['friendly_url'] = Strings::rewrite($data['title']);
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        if($product['type'] == 'listing_membership') {
            $listing_array = array(
                'user_id'=>$user['id'],
                'status'=>(((($product['total'] == 0.00 OR !$_GET['create_invoice']) AND $product['activate'] == 'payment') OR $product['activate'] == 'immediate' OR $product['activate'] == 'approved') ? 'active' : 'pending'),
                'date'=>(string) $data['date'],
                'title'=>(string) $data['title'],
                'priority'=> (int) $product['priority'],
                'priority_calculated'=> (int) $product['priority'],
                'friendly_url'=>(string) Strings::rewrite($data['friendly_url']),
                'description'=>(string) $data['description'],
                'description_short'=>(string) Strings::limit_characters($data['description_short'],$product['short_description_size']),
                'meta_title'=>(string) $data['meta_title'],
                'meta_description'=>(string) $data['meta_description'],
                'meta_keywords'=>(string) $data['meta_keywords'],
                'keywords'=>(string) $data['keywords'],
                'location_id'=>(int) $data['location_id'],
                'hours'=>(string) $data['hours'],
                'phone'=>(string) $data['phone'],
                'fax'=>(string) $data['fax'],
                'listing_address1'=>(string) $data['listing_address1'],
                'listing_address2'=>(string) $data['listing_address2'],
                'listing_zip'=>(string) $data['listing_zip'],
                'location_text_1'=>(string) $data['location_text_1'],
                'location_text_2'=>(string) $data['location_text_2'],
                'location_text_3'=>(string) $data['location_text_3'],
                'latitude'=>(float) $data['latitude'],
                'longitude'=>(float) $data['longitude'],
                'www'=>standardize_url((string) $data['www']),
                'ip'=>get_ip_address(),
                'mail'=>(string) $data['mail'],
                'primary_category_id'=>(int) $data['primary_category_id'],
                'comment'=>(string) $data['comment'],
                'logo'=>$data['logo'],
                'logo_background'=>$data['logo_background'],
                'claimed'=>$data['claimed'],
                'location_search_text'=>trim(preg_replace('/,+/',',',$PMDR->get('Locations')->getPathString($data['location_id']).','.$data['listing_address1'].','.$data['listing_address2'].','.$data['location_text_1'].','.$data['location_text_2'].','.$data['location_text_3'].','.$data['listing_zip'].','.$PMDR->getConfig('map_city_static').','.$PMDR->getConfig('map_state_static').','.$PMDR->getConfig('map_country_static')),','),
                'facebook_page_id'=>(string) $data['facebook_page_id'],
                'twitter_id'=>(string) $data['twitter_id'],
                'google_page_id'=>(string) $data['google_page_id'],
                'linkedin_id'=>(string) $data['linkedin_id'],
                'linkedin_company_id'=>(string) $data['linkedin_company_id'],
                'pinterest_id'=>(string) $data['pinterest_id'],
                'youtube_id'=>(string) $data['youtube_id'],
                'foursquare_id'=>(string) $data['foursquare_id'],
                'instagram_id'=>(string) $data['instagram_id']
            );

            $membership_fields = $db->MetaColumnNames(T_MEMBERSHIPS);
            unset($membership_fields[0]);
            foreach($membership_fields as $field) {
                if(strstr($field,'custom_')) {
                    if(is_array($data[str_replace('_allow','',$field)])) {
                        $listing_array[str_replace('_allow','',$field)] = (string) implode("\n",$data[str_replace('_allow','',$field)]);
                    } else {
                        $listing_array[str_replace('_allow','',$field)] = (string) $data[str_replace('_allow','',$field)];
                    }
                    $listing_array[$field] = (string) $product[$field];
                } else {
                    $listing_array[$field] = (string) $product[$field];
                }
            }

            $type_id = $PMDR->get('Listings')->insert($listing_array);
            $PMDR->get('Listings')->updateCategories($type_id, $data['categories'], $data['primary_category_id']);

            if($product['images_limit'] > 0) {
                for($x=1; $x <= $product['images_limit']; $x++) {
                    if(!empty($data['image'.$x])) {
                        $PMDR->get('Images')->insert(array('listing_id'=>$type_id,'title'=>'Image '.$x,'image'=>$data['image'.$x]));
                    }
                }
            }
        }
        if(!isset($_GET['order_id'])) {
            if($product['total'] != 0.00 AND $_GET['create_invoice']) {
                $invoice_id = $PMDR->get('Invoices')->insert(
                    array(
                        'user_id'=>$user['id'],
                        'type'=>$product['type'],
                        'type_id'=>$type_id,
                        'date_due'=>$product['next_due_date'],
                        'subtotal'=>$product['subtotal'],
                        'tax'=>(float) $product['tax'],
                        'tax_rate'=>(float) $product['tax_rate'],
                        'tax2'=>(float) $product['tax2'],
                        'tax_rate2'=>(float) $product['tax_rate2'],
                        'total'=>$product['total'],
                        'next_due_date'=>$product['future_due_date'],
                        'product_name'=>$product['product_name'],
                        'product_group_name'=>$product['product_group_name']
                    )
                );
            }
            $order_id = $PMDR->get('Orders')->insert(
                array(
                    'order_id'=>$PMDR->get('Orders')->getRandomOrderID(),
                    'type'=>$product['type'],
                    'type_id'=>$type_id,
                    'invoice_id'=>(int) $invoice_id,
                    'user_id'=>$user['id'],
                    'pricing_id'=>$product['pricing_id'],
                    'date'=>$PMDR->get('Dates')->dateTimeNow(),
                    'status'=>'active',
                    'amount_recurring'=>$product['recurring_total'],
                    'period'=>$product['period'],
                    'period_count'=>$product['period_count'],
                    // Because the admin has a choice on whether to create an invoice,  if they don't we automatically set the next due date to the next invoice date
                    'next_due_date'=>(($product['total'] != 0.00 AND !$_GET['create_invoice']) ? $product['next_invoice_date'] : $product['next_due_date']),
                    'future_due_date'=>$product['future_due_date'],
                    'next_invoice_date'=>$product['next_invoice_date'],
                    'taxed'=>$product['taxed'],
                    'upgrades'=>$product['upgrades'],
                    'renewable'=>$product['renewable'],
                    'suspend_overdue_days'=>$product['suspend_overdue_days'],
                    'ip_address'=>get_ip_address()
                )
            );

            $PMDR->get('Invoices')->update(array('order_id'=>$order_id),$invoice_id);
        } else {
            $db->Execute("UPDATE ".T_ORDERS." SET type_id=? WHERE id=?",array($type_id,$_GET['order_id']));
        }
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($order_id,$PMDR->getLanguage('admin_orders'))),'insert');
        redirect(BASE_URL_ADMIN.'/admin_orders.php');
    }
}

$template_content->set('content',$form->toHTML());
if(!isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_orders_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>