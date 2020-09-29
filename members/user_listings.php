<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_listings','general_locations','email_templates','user_orders'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_listings_edit.tpl');

$category_count = $PMDR->get('Categories')->getCount();
$location_count = $PMDR->get('Locations')->getCount();
$listing = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($_GET['id']));
$listing['categories'] = $db->GetCol("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=? AND cat_id!=?",array($listing['id'],$listing['primary_category_id']));

if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('edit'));

$order = $db->GetRow("SELECT * FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id']));
$product = $PMDR->get('Products')->getByPricingID($order['pricing_id'],$user['id']);

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_listings_edit'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_edit'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

$form = $PMDR->getNew('Form');
$form->enctype = 'multipart/form-data';

$form->addFieldSet('listing',array('legend'=>$PMDR->getLanguage('user_listings_edit')));
$form->addField('title','text',array('label'=>$PMDR->getLanguage('user_listings_title'),'fieldset'=>'listing','counter'=>$listing['title_size']));
$form->addValidator('title',new Validate_Banned_Words());
if($listing['friendly_url_allow']) {
    $form->addField('friendly_url','text',array('label'=>$PMDR->getLanguage('user_listings_friendly_url'),'fieldset'=>'listing'));
    $form->addJavascript('title','onblur','$(document).ready(function(){$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});});');
}
if($category_count > 1) {
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
            $form->addField('primary_category_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('user_listings_primary_category'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'])));
        }
    }
    $form->addValidator('primary_category_id',new Validate_NonEmpty());

    if($listing['category_limit'] > 1) {
        if($PMDR->getConfig('category_select_type') == 'tree_select' OR $PMDR->getConfig('category_select_type') == 'tree_select_multiple') {
            if($listing['category_limit'] == 2) {
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
            if($listing['category_limit'] == 2) {
                $form->addField('categories','tree_select_cascading',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'],'limit'=>(intval($listing['category_limit'])-1))));
            } else {
                $form->addField('categories','tree_select_cascading_multiple',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'],'limit'=>(intval($listing['category_limit'])-1))));
            }
        } else {
            if($listing['category_limit'] == 2) {
                $form->addField('categories','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'])));
            } else {
                $form->addField('categories','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','checkall'=>false,'limit'=>(intval($listing['category_limit'])-1),'options'=>array('type'=>'category_tree','closed'=>0,'filter'=>$product['categories'])));
            }
        }
    }
} else {
    $form->addField('primary_category_id','hidden',array('label'=>$PMDR->getLanguage('user_listings_categories'),'fieldset'=>'listing','value'=>($primary_category_id = $PMDR->get('Categories')->getOneID())));
}

if($listing['short_description_size']) {
    $form->addField('description_short','textarea',array('label'=>$PMDR->getLanguage('user_listings_short_description'),'fieldset'=>'listing','counter'=>$listing['short_description_size']));
    $form->addValidator('description_short',new Validate_Banned_Words());
}
if($listing['description_size']) {
    if($listing['html_editor_allow']) {
        if($listing['description_images_limit']) {
            $form->addField('description','htmleditor',array('label'=>$PMDR->getLanguage('user_listings_description'),'fieldset'=>'listing','counter'=>$listing['description_size'],'listing_id'=>$listing['id'],'browse'=>$listing['images_limit']));
        } else {
            $form->addField('description','htmleditor',array('label'=>$PMDR->getLanguage('user_listings_description'),'fieldset'=>'listing','counter'=>$listing['description_size']));
        }
    } else {
        $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('user_listings_description'),'fieldset'=>'listing','counter'=>$listing['description_size']));
    }
    $form->addValidator('description',new Validate_Banned_Words());
    $form->addValidator('description', new Validate_Length($listing['description_size']));
    $form->addValidator('description', new Validate_Image_Tag_Limit($listing['description_images_limit']));
}

if($listing['keywords_limit']) {
    $form->addField('keywords','textarea',array('label'=>$PMDR->getLanguage('user_listings_keywords'),'fieldset'=>'listing'));
    $form->addValidator('keywords',new Validate_Word_Count($listing['keywords_limit']));
    $form->addValidator('keywords',new Validate_Banned_Words());
    $form->addFieldNote('keywords',$PMDR->getLanguage('user_listings_limit').': '.$listing['keywords_limit']);
}
if($listing['meta_title_size']) {
    $form->addField('meta_title','text',array('label'=>$PMDR->getLanguage('user_listings_meta_title'),'fieldset'=>'listing','counter'=>$listing['meta_title_size']));
    $form->addValidator('meta_title',new Validate_Banned_Words());
}
if($listing['meta_description_size']) {
    $form->addField('meta_description','textarea',array('label'=>$PMDR->getLanguage('user_listings_meta_description'),'fieldset'=>'listing','counter'=>$listing['meta_description_size']));
    $form->addValidator('meta_description',new Validate_Banned_Words());
}
if($listing['meta_keywords_limit']) {
    $form->addField('meta_keywords','textarea',array('label'=>$PMDR->getLanguage('user_listings_meta_keywords'),'fieldset'=>'listing'));
    $form->addValidator('meta_keywords',new Validate_Word_Count($listing['meta_keywords_limit']));
    $form->addValidator('meta_keywords',new Validate_Banned_Words());
    $form->addFieldNote('meta_keywords',$PMDR->getLanguage('user_listings_limit').': '.$listing['meta_keywords_limit']);
}
if($listing['phone_allow']) {
    $form->addField('phone','text',array('label'=>$PMDR->getLanguage('user_listings_phone'),'fieldset'=>'listing'));
}
if($listing['fax_allow']) {
    $form->addField('fax','text',array('label'=>$PMDR->getLanguage('user_listings_fax'),'fieldset'=>'listing'));
}
if($listing['address_allow']) {
    $form->addField('listing_address1','text',array('label'=>$PMDR->getLanguage('user_listings_address1'),'fieldset'=>'listing'));
    $form->addField('listing_address2','text',array('label'=>$PMDR->getLanguage('user_listings_address2'),'fieldset'=>'listing'));
    $form->addValidator('listing_address1',new Validate_Banned_Words());
    $form->addValidator('listing_address2',new Validate_Banned_Words());
}
if($location_count > 1) {
    if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
        $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'listing','first_option'=>'','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 ORDER BY left_")));
    } else {
        $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'listing','value'=>'','options'=>array('type'=>'location_tree','search'=>true)));
    }
    $form->addValidator('location_id',new Validate_NonEmpty());
} else {
    $form->addField('location_id','hidden',array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'listing','value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
}

if($PMDR->getConfig('location_text_1')) {
    $listing['location_text_1_allow'] = true;
    $form->addField('location_text_1','text',array('label'=>$PMDR->getLanguage('general_locations_text_1'),'fieldset'=>'listing'));
}
if($PMDR->getConfig('location_text_2')) {
    $listing['location_text_2_allow'] = true;
    $form->addField('location_text_2','text',array('label'=>$PMDR->getLanguage('general_locations_text_2'),'fieldset'=>'listing'));
}
if($PMDR->getConfig('location_text_3')) {
    $listing['location_text_3_allow'] = true;
    $form->addField('location_text_3','text',array('label'=>$PMDR->getLanguage('general_locations_text_3'),'fieldset'=>'listing'));
}
if($listing['zip_allow']) {
    $form->addField('listing_zip','text',array('label'=>$PMDR->getLanguage('user_listings_zip_code'),'fieldset'=>'listing'));
}
if($listing['hours_allow']) {
    $form->addField('hours','hours',array('label'=>$PMDR->getLanguage('user_listings_hours'),'fieldset'=>'listing','options'=>array('hours_24'=>true,'hours_24_label'=>$PMDR->getLanguage('user_listings_hours_24'))));
}
if($listing['coordinates_allow']) {
    $form->addField('latitude','text',array('label'=>$PMDR->getLanguage('user_listings_latitude'),'fieldset'=>'listing'));
    $form->addField('longitude','text',array('label'=>$PMDR->getLanguage('user_listings_longitude'),'fieldset'=>'listing'));
    $form->addPicker('longitude','coordinates',null,array('label'=>$PMDR->getLanguage('user_listings_select_coordinates')));
}

if($listing['www_allow']) {
    $form->addField('www','text',array('label'=>$PMDR->getLanguage('user_listings_website'),'fieldset'=>'listing'));
    $form->addValidator('www',new Validate_URL(false));
    $form->addFieldNote('www',$PMDR->getLanguage('user_listings_www_example'));
    if(ADDON_LINK_CHECKER AND $listing['require_reciprocal'] AND !$PMDR->getConfig('reciprocal_field')) {
        $form->addFieldNote('www',$PMDR->getLanguage('user_listings_reciprocal_instructions',array($PMDR->get('LinkChecker')->check_url)));
    }
}
if($listing['email_allow']) {
    $form->addField('mail','text',array('label'=>$PMDR->getLanguage('user_listings_email'),'fieldset'=>'listing'));
    $form->addValidator('mail',new Validate_Email(false));
}
if($listing['social_links_allow']) {
    $form->addField('facebook_page_id','text_group',array('fieldset'=>'listing','prepend'=>'http://facebook.com/'));
    $form->addField('twitter_id','text_group',array('fieldset'=>'listing','prepend'=>'http://twitter.com/'));
    $form->addField('google_page_id','text_group',array('fieldset'=>'listing','prepend'=>'http://plus.google.com/'));
    $form->addField('linkedin_id','text_group',array('fieldset'=>'listing','prepend'=>'http://linkedin.com/pub/'));
    $form->addField('linkedin_company_id','text_group',array('fieldset'=>'listing','prepend'=>'http://linkedin.com/company/'));
    $form->addField('pinterest_id','text_group',array('fieldset'=>'listing','prepend'=>'http://pinterest.com/'));
    $form->addField('youtube_id','text_group',array('fieldset'=>'listing','prepend'=>'http://youtube.com/user/'));
    $form->addField('foursquare_id','text_group',array('fieldset'=>'listing','prepend'=>'http://foursquare.com/'));
    $form->addField('instagram_id','text_group',array('fieldset'=>'listing','prepend'=>'http://instagram.com/'));
}

$fields = $PMDR->get('Fields')->addToForm($form,'listings',array('fieldset'=>'listing','filter'=>$listing,'category'=>$listing['primary_category_id'],'editable'=>true,'admin_only'=>false));

if(ADDON_LINK_CHECKER AND $listing['require_reciprocal'] AND $PMDR->getConfig('reciprocal_field') AND $listing[$PMDR->getConfig('reciprocal_field').'_allow']) {
    $form->addFieldNote($PMDR->getConfig('reciprocal_field'),$PMDR->getLanguage('user_listings_reciprocal_instructions',array($PMDR->get('LinkChecker')->check_url)));
}

if(ADDON_LINK_CHECKER AND $listing['require_reciprocal']) {
    if($links = $PMDR->get('Site_Links')->getLinks($listing['id'])) {
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

if($listing['logo_allow']) {
    $form->addField('logo','file',array('label'=>$PMDR->getLanguage('user_listings_logo'),'fieldset'=>'listing'));
    $form->addValidator('logo',new Validate_Image($PMDR->getConfig('image_logo_width'),$PMDR->getConfig('image_logo_height'),$PMDR->getConfig('image_logo_size'),explode(',',$PMDR->getConfig('logos_formats'))));
    if($logo = get_file_url(LOGO_THUMB_PATH.$listing['id'].'.*')) {
        $form->addField('preview','custom',array('label'=>$PMDR->getLanguage('user_listings_logo_current'),'fieldset'=>'listing','html'=>'<img src="'.$logo.'?random='.Strings::random(5).'">'));
        $form->addField('delete_logo','checkbox',array('label'=>$PMDR->getLanguage('user_listings_logo_delete'),'fieldset'=>'listing','value'=>'0'));
    }
}

if($listing['logo_background_allow']) {
    $form->addField('logo_background','file',array('label'=>$PMDR->getLanguage('user_listings_logo_background'),'fieldset'=>'listing'));
    $form->addValidator('logo_background',new Validate_Image($PMDR->getConfig('logo_background_width'),$PMDR->getConfig('logo_background_height'),$PMDR->getConfig('logo_background_size'),explode(',',$PMDR->getConfig('logos_formats'))));
    if($logo_background = get_file_url(LOGO_BACKGROUND_PATH.$listing['id'].'.*')) {
        $form->addField('logo_background_preview','custom',array('label'=>$PMDR->getLanguage('user_listings_logo_background_preview'),'fieldset'=>'listing','html'=>'<img src="'.$logo_background.'?random='.Strings::random(5).'">'));
        $form->addField('logo_background_delete','checkbox',array('label'=>$PMDR->getLanguage('user_listings_logo_background_delete'),'fieldset'=>'listing','value'=>'0'));
    }
}

$form->addValidator('title',new Validate_NonEmpty());

$form->addField('timezone','select',array('label'=>$PMDR->getLanguage('user_listings_timezone'),'fieldset'=>'listing','first_option'=>'','options'=>include(PMDROOT.'/includes/timezones.php')));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

$form->loadValues($listing);

$template_content->set('form',$form);
$template_content->set('fields',$fields);
$template_content->set('listing',$listing);

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if($listing['friendly_url_allow'] OR empty($listing['friendly_url'])) {
        if(!$PMDR->getConfig('mod_rewrite_listings_id')) {
            if($data['friendly_url'] != '') {
                if($db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE friendly_url=? AND id!=?",array($data['friendly_url'],$listing['id']))) {
                    $form->addError('The friendly URL entered is already in use.','friendly_url');
                }
            } else {
                if($db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE friendly_url=? AND id!=?",array($data['friendly_url'] = Strings::rewrite($data['title']),$listing['id']))) {
                    $form->addError('The title is currently in use by another listing.','title');
                }
            }
        } elseif($data['friendly_url'] == '') {
            $data['friendly_url'] = Strings::rewrite($data['title']);
        }
    }
    if(!empty($data['www']) AND $PMDR->getConfig('block_duplicate_urls') AND $duplicate_listing = $db->GetRow("SELECT id, friendly_url FROM ".T_LISTINGS." WHERE www=? AND id!=?",array($data['www'],$listing['id']))) {
        $form->addError($PMDR->getLanguage('user_listings_duplicate_url_error',$PMDR->get('Listings')->getURL($duplicate_listing['id'],$duplicate_listing['friendly_url'])),'www');
    }
    if(ADDON_LINK_CHECKER AND $listing['require_reciprocal']) {
        if(!$PMDR->getConfig('reciprocal_field')) {
            if(!empty($data['www'])) {
                if($PMDR->get('LinkChecker')->checkURL($data['www']) != 'valid') {
                    $form->addError($PMDR->getLanguage('user_listings_reciprocal_error',$PMDR->get('LinkChecker')->check_url),'www');
                }
            }
        } elseif(isset($data[$PMDR->getConfig('reciprocal_field')]) AND $listing[$PMDR->getConfig('reciprocal_field').'_allow']) {
            if($PMDR->get('LinkChecker')->checkURL($data[$PMDR->getConfig('reciprocal_field')]) != 'valid') {
                $form->addError($PMDR->getLanguage('user_listings_reciprocal_error',$PMDR->get('LinkChecker')->check_url),$PMDR->getConfig('reciprocal_field'));
            }
        }
    }
    if($listing['category_limit'] > 1) {
        if(count((array) $data['categories']) >= $listing['category_limit']) {
            $form->addError($PMDR->getLanguage('user_listings_category_limit_error',array($listing['category_limit'])),'categories');
        }
    }
    if($PMDR->getConfig('category_setup') == 0 AND is_array($data['categories'])) {
        foreach($data['categories'] as $category_id) {
            if(!$PMDR->get('Categories')->isLeaf($category_id)) {
                $form->addError($PMDR->getLanguage('user_listings_category_setup_error'),'categories');
                break;
            }
        }
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        if($data['latitude'] == '' OR $data['longitude'] == '') {
            $locations = $PMDR->get('Locations')->getPath($data['location_id']);
            foreach($locations as $loc_key=>$loc_value) {
                $data['location_'.($loc_key+1)] = $loc_value['title'];
                if($loc_value['disable_geocoding']) {
                    $listing['disable_geocoding'] = true;
                }
            }
            if(!$listing['disable_geocoding']) {
                $map = $PMDR->get('Map');
                $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $data[$PMDR->getConfig('map_country')];
                $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $data[$PMDR->getConfig('map_state')];
                $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $data[$PMDR->getConfig('map_city')];
                if($coordinates = $map->getGeocode($data['listing_address1'], $map_city, $map_state, $map_country, $data['listing_zip'])) {
                    if(abs($coordinates['lat']) > 0 AND abs($coordinates['lon']) > 0) {
                        $data['latitude'] = $coordinates['lat'];
                        $data['longitude'] = $coordinates['lon'];
                    }
                }
            }
        }
        if($PMDR->getConfig('approve_update') AND !$user['moderate_disable']) {
            $db->Execute("REPLACE INTO ".T_UPDATES." (user_id, type, type_id, date) VALUES (?,'listing_membership',?,NOW())",array($listing['user_id'],$listing['id']));
            if($db->Affected_Rows() == 1) {
                $PMDR->get('Email_Templates')->send('admin_update_submitted');
                if($PMDR->getConfig('approve_update_pending')) {
                    $data['status'] = 'suspended';
                }
            }
            $PMDR->addMessage('success',$PMDR->getLanguage('user_listings_submitted_approval'),'update');
        } else {
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_listings'))),'update');
        }
        $PMDR->get('Listings')->update($data, $listing['id']);
        redirect(array('id'=>$listing['id']));
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>