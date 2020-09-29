<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_orders','user_listings','general_locations','email_templates'));

$PMDR->get('Authentication')->authenticate(array('redirect'=>($PMDR->getConfig('login_module_registration_url') ? BASE_URL.MEMBERS_FOLDER.'index.php' : BASE_URL.MEMBERS_FOLDER.'user_account_add.php')));

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$product = $PMDR->get('Products')->getByPricingID($_GET['pricing_id'],$user['id']);

$map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $PMDR->getConfig('map_country');
$map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $PMDR->getConfig('map_state');
$map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $PMDR->getConfig('map_city');

if(!$product OR !$product['active']) {
    // If a pricing ID was attempted, show a message, otherwise redirect silently
    if(isset($_GET['pricing_id'])) {
        $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_pricing_id_unavailable'));
    }
    redirect_url(rebuild_url(array(),array('pricing_id'),false,BASE_URL.MEMBERS_FOLDER.'user_orders_add.php'));
}

if($PMDR->getConfig('product_limit') != '' AND $PMDR->getConfig('product_limit') <= $db->GetOne("SELECT COUNT(*) FROM ".T_ORDERS." WHERE user_id=? AND status IN('active','pending','suspended','fraud')",array($PMDR->get('Session')->get('user_id')))) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_used_limit',intval($PMDR->getConfig('product_limit'))));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_index.php');
}

// Check to see if the product group user limit has been reached
$product_group_count = $db->GetOne("SELECT COUNT(*) FROM ".T_ORDERS." o WHERE o.user_id=? AND o.pricing_id IN(SELECT pp.id FROM ".T_PRODUCTS_PRICING." pp INNER JOIN ".T_PRODUCTS." p WHERE group_id=?) AND o.status IN('active','pending','suspended','fraud')",array($PMDR->get('Session')->get('user_id'),$product['group_id']));
$product_group_limit = $db->GetOne("SELECT user_limit FROM ".T_PRODUCTS_GROUPS." WHERE id=?",array($product['group_id']));
if($product_group_limit != 0 AND $product_group_limit <= $product_group_count) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_used_limit',intval($product_group_limit)));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_index.php');
}
unset($product_group_count,$product_group_limit);

if($product['user_limit'] != 0 AND $product['user_limit'] <= $db->GetOne("SELECT COUNT(*) FROM ".T_ORDERS." WHERE user_id=? AND pricing_id=? AND status IN('active','pending','suspended','fraud')",array($PMDR->get('Session')->get('user_id'),$product['pricing_id']))) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_used_limit',$product['user_limit']));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders_add.php');
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_listings_listing'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_add.php','text'=>$PMDR->getLanguage('user_orders_add')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_add_listing.php?pricing_id='.$product['pricing_id'],'text'=>$PMDR->getLanguage('user_listings_listing')));

$category_count = $PMDR->get('Categories')->getCount();
$location_count = $PMDR->get('Locations')->getCount();

if(!isset($_GET['primary_category_id'])) {
    if($category_count > 1) {
        $form = $PMDR->getNew('Form');
        $form->addFieldSet('listing',array('legend'=>$PMDR->getLanguage('user_listings_primary_category')));
        if($PMDR->getConfig('category_select_type') == 'tree_select' OR $PMDR->getConfig('category_select_type') == 'tree_select_multiple') {
            if($PMDR->getConfig('category_setup') == 0) {
                $form->addField('primary_category_id','tree_select_group',array('label'=>$PMDR->getLanguage('user_listings_primary_category'),'fieldset'=>'listing','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0),$product['categories'])));
            } else {
                $form->addField('primary_category_id','tree_select',array('label'=>$PMDR->getLanguage('user_listings_primary_category'),'fieldset'=>'listing','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0),$product['categories'])));
            }
        } else {
            if($PMDR->getConfig('category_select_type') == 'tree_select_cascading') {
                $form->addField('primary_category_id','tree_select_cascading',array('label'=>$PMDR->getLanguage('user_listings_primary_category'),'fieldset'=>'listing','value'=>'','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'])));
            } else {
                $form->addField('primary_category_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('user_listings_primary_category'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','search'=>true,'closed'=>0,'filter'=>$product['categories'])));
            }
        }
    } elseif($category_count == 1) {
        redirect(array('pricing_id'=>$product['pricing_id'],'primary_category_id'=>$PMDR->get('Categories')->getOneID()));
    } else {
        $PMDR->addMessage('error','At least one category must be added before adding a listing.');
        redirect(BASE_URL.MEMBERS_FOLDER.'user_orders_add.php');
    }
    $form->addValidator('primary_category_id',new Validate_NonEmpty());
    $form->addField('submit_primary','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
} else {
    // Check to make sure the primary category is not closed
    if($PMDR->get('Categories')->isClosed($_GET['primary_category_id'])) {
        redirect(BASE_URL.MEMBERS_FOLDER.'user_orders_add_listing.php',array('pricing_id'=>$_GET['pricing_id']));
    }

    /** @var Form */
    $form = $PMDR->getNew('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('listing',array('legend'=>$PMDR->getLanguage('user_listings_listing')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('user_listings_title'),'fieldset'=>'listing','counter'=>$product['title_size'],'value'=>$user['user_organization']));
    $form->addValidator('title',new Validate_Banned_Words());
    if($product['friendly_url_allow']) {
        $form->addField('friendly_url','text',array('label'=>$PMDR->getLanguage('user_listings_friendly_url'),'fieldset'=>'listing','value'=>Strings::rewrite($user['user_organization'])));
        $form->addJavascript('title','onblur','$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});');
    }

    if($category_count > 1 AND $product['category_limit'] > 1) {
        if($PMDR->getConfig('category_select_type') == 'tree_select' OR $PMDR->getConfig('category_select_type') == 'tree_select_multiple') {
            if($product['category_limit'] == 2) {
                if($PMDR->getConfig('category_setup') == 0) {
                    $form->addField('categories','tree_select_group',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0),$product['categories'])));
                } else {
                    $form->addField('categories','tree_select',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0),$product['categories'])));
                }
            } else {
                if($PMDR->getConfig('category_setup') == 0) {
                    $form->addField('categories','tree_select_multiple_group',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0),$product['categories'])));
                } else {
                    $form->addField('categories','tree_select_multiple',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0),$product['categories'])));
                }
            }
        } elseif($PMDR->getConfig('category_select_type') == 'tree_select_cascading' OR $PMDR->getConfig('category_select_type') == 'tree_select_cascading_multiple') {
            if($product['category_limit'] == 2) {
                $form->addField('categories','tree_select_cascading',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'],'limit'=>(intval($product['category_limit'])-1))));
            } else {
                $form->addField('categories','tree_select_cascading_multiple',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'],'limit'=>(intval($product['category_limit'])-1))));
            }
        } else {
            if($product['category_limit'] == 2) {
                $form->addField('categories','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','search'=>true,'closed'=>0,'filter'=>$product['categories'])));
            } else {
                $form->addField('categories','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','checkall'=>false,'options'=>array('type'=>'category_tree','search'=>true,'closed'=>0,'filter'=>$product['categories']),'limit'=>(intval($product['category_limit'])-1)));
            }
        }
    }
    $form->addField('primary_category_id','hidden',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','value'=>$_GET['primary_category_id']));

    if($product['short_description_size']) {
        $form->addField('description_short','textarea',array('label'=>$PMDR->getLanguage('user_listings_short_description'),'fieldset'=>'listing','counter'=>$product['short_description_size']));
        $form->addValidator('description_short',new Validate_Banned_Words());
    }
    if($product['description_size']) {
        if($product['html_editor_allow']) {
            $form->addField('description','htmleditor',array('label'=>$PMDR->getLanguage('user_listings_description'),'fieldset'=>'listing','counter'=>$product['description_size']));
        } else {
            $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('user_listings_description'),'fieldset'=>'listing','counter'=>$product['description_size']));
        }
        $form->addValidator('description',new Validate_Banned_Words());
        $form->addValidator('description', new Validate_Length($product['description_size']));
    }
    if($product['keywords_limit']) {
        $form->addField('keywords','textarea',array('label'=>$PMDR->getLanguage('user_listings_keywords'),'fieldset'=>'listing'));
        $form->addValidator('keywords',new Validate_Word_Count($product['keywords_limit']));
        $form->addValidator('keywords',new Validate_Banned_Words());
        $form->addFieldNote('keywords',$PMDR->getLanguage('user_listings_limit').': '.$product['keywords_limit']);
    }
    if($product['meta_title_size']) {
        $form->addField('meta_title','text',array('label'=>$PMDR->getLanguage('user_listings_meta_title'),'fieldset'=>'listing','counter'=>$product['meta_title_size']));
        $form->addValidator('meta_title',new Validate_Banned_Words());
    }
    if($product['meta_description_size']) {
        $form->addField('meta_description','textarea',array('label'=>$PMDR->getLanguage('user_listings_meta_description'),'fieldset'=>'listing','counter'=>$product['meta_description_size']));
        $form->addValidator('meta_description',new Validate_Banned_Words());
    }
    if($product['meta_keywords_limit']) {
        $form->addField('meta_keywords','textarea',array('label'=>$PMDR->getLanguage('user_listings_meta_keywords'),'fieldset'=>'listing'));
        $form->addValidator('meta_keywords',new Validate_Word_Count($product['meta_keywords_limit']));
        $form->addValidator('meta_keywords',new Validate_Banned_Words());
        $form->addFieldNote('meta_keywords',$PMDR->getLanguage('user_listings_limit').': '.$product['meta_keywords_limit']);
    }

    if($product['phone_allow']) {
        $form->addField('phone','text',array('label'=>$PMDR->getLanguage('user_listings_phone'),'fieldset'=>'listing','value'=>$user['user_phone']));
    }

    if($product['fax_allow']) {
        $form->addField('fax','text',array('label'=>$PMDR->getLanguage('user_listings_fax'),'fieldset'=>'listing','value'=>$user['user_fax']));
    }

    if($product['address_allow']) {
        $form->addField('listing_address1','text',array('label'=>$PMDR->getLanguage('user_listings_address1'),'fieldset'=>'listing','value'=>$user['user_address1']));
        $form->addField('listing_address2','text',array('label'=>$PMDR->getLanguage('user_listings_address2'),'fieldset'=>'listing','value'=>$user['user_address2']));
        $form->addValidator('listing_address1',new Validate_Banned_Words());
        $form->addValidator('listing_address2',new Validate_Banned_Words());
    }

    if($map_country == 'location_1') {
        $location_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE title=? AND parent_id=1",array($user['user_country']));
        if($map_state == 'location_2') {
            $location_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE title=? AND parent_id=?",array($user['user_state'],$location_id));
            if($map_city == 'location_3') {
                $location_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE title=? AND parent_id=?",array($user['user_city'],$location_id));
            }
        }
    } elseif($map_state == 'location_1') {
        $location_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE title=? AND parent_id=1",array($user['user_state']));
        if($map_city == 'location_2') {
            $location_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE title=? AND parent_id=?",array($user['user_city'],$location_id));
        }
    } elseif($map_city == 'location_1') {
        $location_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE title=? AND parent_id=1",array($user['user_city']));
    }

    if($location_count > 1) {
        if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'listing','first_option'=>'','value'=>$location_id,'options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE id!=1 AND closed=0 ORDER BY left_")));
        } else {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'listing','options'=>array('type'=>'location_tree','closed'=>0,'search'=>true),'value'=>$location_id));
        }
        $form->addValidator('location_id',new Validate_NonEmpty());
    } else {
        $form->addField('location_id','hidden',array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'listing','value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
    }

    for($x = 1; $x <= 3; $x++) {
        if($PMDR->getConfig('location_text_'.$x)) {
            // Add this to the membership array so we know to display it in the template
            $product['location_text_'.$x] = true;
            if($map_city == 'location_text_'.$x) {
                $value = $user['user_city'];
            } elseif($map_state == 'location_text_'.$x) {
                $value = $user['user_state'];
            } elseif($map_country == 'location_text_'.$x) {
                $value = $user['user_country'];
            }
            $form->addField('location_text_'.$x,'text',array('label'=>$PMDR->getLanguage('general_locations_text_'.$x),'fieldset'=>'listing','value'=>$value));
        }
    }
    if($product['hours_allow']) {
        $form->addField('hours','hours',array('label'=>$PMDR->getLanguage('user_listings_hours'),'fieldset'=>'listing','options'=>array('hours_24'=>true,'hours_24_label'=>$PMDR->getLanguage('user_listings_hours_24'))));
    }
    if($product['zip_allow']) {
        $form->addField('listing_zip','text',array('label'=>$PMDR->getLanguage('user_listings_zip_code'),'fieldset'=>'listing','value'=>$user['user_zip']));
    }
    if($product['coordinates_allow']) {
        $form->addField('latitude','text',array('label'=>$PMDR->getLanguage('user_listings_latitude'),'fieldset'=>'listing'));
        $form->addField('longitude','text',array('label'=>$PMDR->getLanguage('user_listings_longitude'),'fieldset'=>'listing'));
        $form->addPicker('longitude','coordinates',null,array('label'=>$PMDR->getLanguage('user_listings_select_coordinates'),'coordinates'=>$PMDR->getConfig('map_select_coordinates'),'zoom'=>$PMDR->getConfig('map_select_zoom'),'marker'=>false));
    }
    if($product['www_allow']) {
        $form->addField('www','text',array('label'=>$PMDR->getLanguage('user_listings_website'),'fieldset'=>'listing'));
        $form->addValidator('www',new Validate_URL(false));
        $form->addFieldNote('www',$PMDR->getLanguage('user_orders_www_example'));
        $form->addValidator('www',new Validate_Banned_URL());
        if(ADDON_LINK_CHECKER AND $product['require_reciprocal'] AND !$PMDR->getConfig('reciprocal_field')) {
            $form->addFieldNote('www',$PMDR->getLanguage('user_listings_reciprocal_instructions',array($PMDR->get('LinkChecker')->check_url)));
        }
    }

    if($product['email_allow']) {
        $form->addField('mail','text',array('label'=>$PMDR->getLanguage('user_listings_email'),'fieldset'=>'listing','value'=>$user['user_email']));
        $form->addValidator('mail',new Validate_Email(false));
    }
    if($product['social_links_allow']) {
        $form->addField('facebook_page_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_facebook_page_id'),'fieldset'=>'listing','prepend'=>'http://facebook.com/'));
        $form->addField('twitter_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_twitter_id'),'fieldset'=>'listing','prepend'=>'http://twitter.com/'));
        $form->addField('google_page_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_google_page_id'),'fieldset'=>'listing','prepend'=>'http://plus.google.com/'));
        $form->addField('linkedin_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_linkedin_id'),'fieldset'=>'listing','prepend'=>'http://linkedin.com/pub/'));
        $form->addField('linkedin_company_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_linkedin_company_id'),'fieldset'=>'listing','prepend'=>'http://linkedin.com/company/'));
        $form->addField('pinterest_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_pinterest_id'),'fieldset'=>'listing','prepend'=>'http://pinterest.com/'));
        $form->addField('youtube_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_youtube_id'),'fieldset'=>'listing','prepend'=>'http://youtube.com/user/'));
        $form->addField('foursquare_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_foursquare_id'),'fieldset'=>'listing','prepend'=>'http://foursquare.com/'));
        $form->addField('instagram_id','text_group',array('label'=>$PMDR->getLanguage('user_listings_instagram_id'),'fieldset'=>'listing','prepend'=>'http://instagram.com/'));
    }

    $fields = $PMDR->get('Fields')->addToForm($form,'listings',array('fieldset'=>'listing','filter'=>$product,'category'=>$_GET['primary_category_id'],'admin_only'=>false));

    if(ADDON_LINK_CHECKER AND $product['require_reciprocal'] AND $PMDR->getConfig('reciprocal_field') AND $product[$PMDR->getConfig('reciprocal_field').'_allow']) {
        $form->addFieldNote($PMDR->getConfig('reciprocal_field'),$PMDR->getLanguage('user_listings_reciprocal_instructions',array($PMDR->get('LinkChecker')->check_url)));
    }

    if(ADDON_LINK_CHECKER AND $product['require_reciprocal']) {
        if($links = $PMDR->get('Site_Links')->getLinks()) {
            $site_links_template = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'site_links.tpl');
            $site_links_template->set('links',$links);
            $PMDR->loadJavascript('
            <script type="text/javascript">
            $(document).ready(function() {
                $("#site_links").dialog({
                     buttons: {
                        "Close": function() { $(this).dialog("close"); }
                     },
                     width: 700,
                     height: 700,
                     autoOpen: false,
                     modal: true,
                     resizable: false,
                     title: "Link to Us"
                });
                $("#site_links_link").click(function(e) {
                    e.preventDefault();
                    $("#site_links").dialog("open");
                });
            });
            </script>',100);
            $reciprocal_field = 'www';
            if($PMDR->getConfig('reciprocal_field') AND $listing[$PMDR->getConfig('reciprocal_field').'_allow']) {
                $reciprocal_field = $PMDR->getConfig('reciprocal_field');
            }
            $form->addFieldNote($reciprocal_field,'<a id="site_links_link" href="#">View Link Examples</a><div style="display: none;" id="site_links">'.$site_links_template->render().'</div>');
            unset($reciprocal_field);
        }
    }

    if($product['logo_allow']) {
        $form->addField('logo','file',array('label'=>$PMDR->getLanguage('user_listings_logo'),'fieldset'=>'listing'));
        $form->addValidator('logo',new Validate_Image($PMDR->getConfig('image_logo_width'),$PMDR->getConfig('image_logo_height'),$PMDR->getConfig('image_logo_size'),explode(',',$PMDR->getConfig('logos_formats'))));
    }

    if($product['logo_background_allow']) {
        $form->addField('logo_background','file',array('label'=>$PMDR->getLanguage('user_listings_logo_background'),'fieldset'=>'listing'));
        $form->addValidator('logo_background',new Validate_Image($PMDR->getConfig('logo_background_width'),$PMDR->getConfig('logo_background_height'),$PMDR->getConfig('logo_background_size'),explode(',',$PMDR->getConfig('logos_formats'))));
        $form->addFieldNote('logo_background',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('logo_background_size')));
    }

    if($product['images_limit'] > 0) {
        for($x=1; $x <= $product['images_limit']; $x++) {
            $form->addField('image'.$x,'file',array('label'=>$PMDR->getLanguage('user_listings_images').' '.$x,'fieldset'=>'listing'));
            $form->addValidator('image'.$x,new Validate_Image($PMDR->getConfig('gallery_image_width'),$PMDR->getConfig('gallery_image_height'),$PMDR->getConfig('gallery_image_size'),explode(',',$PMDR->getConfig('images_formats'))));
        }
    }
    $form->addField('timezone','select',array('label'=>$PMDR->getLanguage('user_listings_timezone'),'fieldset'=>'listing','first_option'=>'','options'=>include(PMDROOT.'/includes/timezones.php')));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

    if(isset($_GET['copy'])) {
        if($listing_copy = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=? AND user_id=?",array($_GET['copy'],$user['id']))) {
            $listing_copy['categories'] = $db->GetCol("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=? AND cat_id!=?",array($listing_copy['id'],$listing_copy['primary_category_id']));
            $PMDR->addMessage('notice','Information copied from listing "'.$listing_copy['title'].'" (ID: '.$listing_copy['id'].')');
            $listing_copy['title'] .= ' (Copy)';
            $form->loadValues($listing_copy);
        }
    }
}

if($form->wasSubmitted('submit_primary')) {
    $data = $form->loadValues();
    if($PMDR->getConfig('category_setup') == 0) {
        if(!$PMDR->get('Categories')->isLeaf($data['primary_category_id'])) {
            $form->addError($PMDR->getLanguage('user_listings_category_setup_error'),'primary_category_id');
        }
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        redirect_url(rebuild_url(array('primary_category_id'=>$data['primary_category_id'],'location_id'=>$_GET['location_id'])));
    }
}

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    $data['www'] = standardize_url($data['www']);
    $data['categories'] = array_filter((array) $data['categories']);
    if(!$PMDR->getConfig('mod_rewrite_listings_id')) {
        if($data['friendly_url'] != '') {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE friendly_url=?",array($data['friendly_url']))) {
                $form->addError('The friendly URL entered is already in use.','friendly_url');
            }
        } else {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE friendly_url=? AND friendly_url !=''",array($data['friendly_url'] = Strings::rewrite($data['title'])))) {
                $form->addError('The title is currently in use by another listing.','title');
            }
        }
    } elseif($data['friendly_url'] == '') {
        $data['friendly_url'] = Strings::rewrite($data['title']);
    }
    if(!empty($data['www']) AND $PMDR->getConfig('block_duplicate_urls') AND $listing = $db->GetRow("SELECT id, friendly_url FROM ".T_LISTINGS." WHERE www=?",array($data['www']))) {
        $form->addError($PMDR->getLanguage('user_listings_duplicate_url_error',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'])),'www');
    }
    if(ADDON_LINK_CHECKER AND $product['require_reciprocal']) {
        if(!$PMDR->getConfig('reciprocal_field')) {
            if(!empty($data['www'])) {
                if($PMDR->get('LinkChecker')->checkURL($data['www']) != 'valid') {
                    $form->addError($PMDR->getLanguage('user_listings_reciprocal_error',$PMDR->get('LinkChecker')->check_url),'www');
                }
            }
        } elseif(isset($data[$PMDR->getConfig('reciprocal_field')]) AND $product[$PMDR->getConfig('reciprocal_field').'_allow']) {
            if($PMDR->get('LinkChecker')->checkURL($data[$PMDR->getConfig('reciprocal_field')]) != 'valid') {
                $form->addError($PMDR->getLanguage('user_listings_reciprocal_error',$PMDR->get('LinkChecker')->check_url),$PMDR->getConfig('reciprocal_field'));
            }
        }
    }
    if($product['category_limit'] > 1) {
        if(count((array) $data['categories']) >= $product['category_limit']) {
            $form->addError($PMDR->getLanguage('user_listings_category_limit_error',array($product['category_limit'])),'categories');
        }
    }
    if($PMDR->getConfig('category_setup') == 0) {
        foreach((array) $data['categories'] as $category_id) {
            if(!$PMDR->get('Categories')->isLeaf($category_id)) {
                $form->addError($PMDR->getLanguage('user_listings_category_setup_error'),'categories');
                break;
            }
        }
    }

    $PMDR->get('Plugins')->run_hook('user_orders_add_listing_submit_validation');

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['user_id'] = $PMDR->get('Session')->get('user_id');

        $data['location_search_text'] = trim(preg_replace('/,+/',',',$PMDR->get('Locations')->getPathString($data['location_id']).','.$data['listing_address1'].','.$data['listing_address2'].','.$data['location_text_1'].','.$data['location_text_2'].','.$data['location_text_3'].','.$data['listing_zip'].','.$PMDR->getConfig('map_city_static').','.$PMDR->getConfig('map_state_static').','.$PMDR->getConfig('map_country_static')),',');

        if($product['activate'] == 'payment' AND $product['total'] == 0.00) {
            $status = 'active';
        } elseif($product['activate'] == 'immediate') {
            $status = 'active';
        } elseif($product['activate'] == 'approval' AND $user['moderate_disable']) {
            $status = 'active';
        } else {
            $status = 'pending';
        }

        $listing_array = array(
            'user_id'=>$data['user_id'],
            'status'=>$status,
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
            'location_search_text'=>(string) $data['location_search_text'],
            'latitude'=>(float) $data['latitude'],
            'longitude'=>(float) $data['longitude'],
            'www'=>standardize_url((string) $data['www']),
            'ip'=>get_ip_address(),
            'date'=>$PMDR->get('Dates')->dateTimeNow(),
            'mail'=>(string) $data['mail'],
            'primary_category_id'=>(int) $data['primary_category_id'],
            'comment'=>(string) $data['comment'],
            'logo'=>$data['logo'],
            'claimed'=>1,
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

        unset($status);

        $membership_fields = $db->MetaColumnNames(T_MEMBERSHIPS);
        unset($membership_fields[0]);
        foreach($membership_fields as $field) {
            if(strstr($field,'custom_')) {
                if(is_array($data[str_replace('_allow','',$field)])) {
                    $listing_array[str_replace('_allow','',$field)] = (string) @implode("\n",$data[str_replace('_allow','',$field)]);
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

        if($product['total']) {
            $invoice_id = $PMDR->get('Invoices')->insert(
                array(
                    'user_id'=>$data['user_id'],
                    'type'=>$product['type'],
                    'type_id'=>$type_id,
                    'date_due'=>$product['next_due_date'],
                    'subtotal'=>$product['subtotal'],
                    'tax'=>(float) $product['tax'],
                    'tax_rate'=>(float) $product['tax_rate'],
                    'tax2'=>(float) $product['tax2'],
                    'tax_rate2'=>(float) $product['tax_rate2'],
                    'total'=>$product['total'],
                    'product_name'=>$product['product_name'],
                    'product_group_name'=>$product['product_group_name'],
                    'next_due_date'=>$product['future_due_date'],
                    'affiliate_program_tracking_code'=>value($_COOKIE,$PMDR->getConfig('affiliate_program_cookie'))
                )
            );
            $PMDR->get('Invoices')->sendInvoiceCreatedEmail($invoice_id);
        }

        $order_id = $PMDR->get('Orders')->insert(
            array(
                'order_id'=>($order_id_random = $PMDR->get('Orders')->getRandomOrderID()),
                'type'=>$product['type'],
                'type_id'=>$type_id,
                'invoice_id'=>(int) $invoice_id,
                'user_id'=>$data['user_id'],
                'date'=>$PMDR->get('Dates')->dateTimeNow(),
                'status'=>'pending',
                'pricing_id'=>$product['pricing_id'],
                'amount_recurring'=>$product['recurring_total'],
                'period'=>$product['period'],
                'period_count'=>$product['period_count'],
                'next_due_date'=>$product['next_due_date'],
                'future_due_date'=>$product['future_due_date'],
                'next_invoice_date'=>$product['next_invoice_date'],
                'taxed'=>$product['taxed'],
                'upgrades'=>$product['upgrades'],
                'renewable'=>$product['renewable'],
                'suspend_overdue_days'=>$product['suspend_overdue_days'],
                'ip_address'=>get_ip_address(),
                'affiliate_program_tracking_code'=>value($_COOKIE,$PMDR->getConfig('affiliate_program_cookie'))
            )
        );

        $PMDR->get('Invoices')->update(array('order_id'=>$order_id),$invoice_id);

        $PMDR->get('Email_Templates')->send('order_submitted',array('to'=>$user['user_email'],'order_id'=>$order_id));
        $PMDR->get('Email_Templates')->send('admin_order_submitted',array('order_id'=>$order_id));

        if($product['total']) {
            // Redirect the user to pay the invoice, which if no further details are needed we send them straight to the gateway
            redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices_pay.php?id='.$invoice_id);
        } else {
            // No invoice to pay, so set appropriate message and redirect to list of listings in user account
            if($product['activate'] == 'approved' AND !$user['moderate_disable']) {
                $PMDR->addMessage('success',$PMDR->getLanguage('user_listings_submitted_pending'));
            } else {
                $PMDR->addMessage('success',$PMDR->getLanguage('user_listings_submitted'));
            }
            redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
        }
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_orders_add_listing.tpl');
$template_content->set('product',$product);
$template_content->set('form',$form);
$template_content->set('fields',$fields);

include(PMDROOT.'/includes/template_setup.php');
?>