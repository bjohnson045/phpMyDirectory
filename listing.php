<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->get('Plugins')->run_hook('listing_begin');

$PMDR->loadLanguage(array('public_listing','general_locations','public_listing_images','public_listing_reviews'));

$listing = $PMDR->get('Listings')->getJoinedUser($_GET['id']);
$listing['url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);

// If listing not found, tell browser not found, else proceed to show listing
if(!$listing['id'] OR ($listing['status'] != 'active' AND $listing['user_id'] != $PMDR->get('Session')->get('user_id') AND @!in_array('admin_login',$_SESSION['admin_permissions']))) {
    $PMDR->get('Error',404);
}

$PMDR->set('og:type','business.business');

if($listing['status'] != 'active') {
    $PMDR->addMessage('notice',$PMDR->getLanguage('public_listing_preview'));
}

if($listing['url'] != URL AND !isset($_GET['action'])) {
    $PMDR->get('Error',301);
    redirect($listing['url']);
}

$PMDR->set('page_header',null);

$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', gallery:{enabled:true}, closeOnContentClick: true });});</script>',20);

$listing_locations = $PMDR->get('Locations')->getPath($listing['location_id']);
foreach($listing_locations as $key=>$location) {
    $listing['location_'.($key+1)] = $location['title'];
    $listing['location_'.($key+1).'_abbreviation'] = $location['title'];
    $listing['location_'.($key+1).'_url'] = $PMDR->get('Locations')->getURL($location['id'],$location['friendly_url_path']);
    if($location['disable_geocoding']) {
        $listing['disable_geocoding'] = true;
    }
}

if($listing['location_id'] AND $listing['location_id'] > 1) {
    $PMDR->set('active_location',array('id'=>$listing['location_id'],'friendly_url_path'=>$listing_locations[(count($listing_locations)-1)]['friendly_url_path']));
}

$map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $listing[$PMDR->getConfig('map_country')];
$map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $listing[$PMDR->getConfig('map_state')];
$map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $listing[$PMDR->getConfig('map_city')];

// Add listing to favorites
if($_GET['action'] == 'addtofavorites') {
    $PMDR->get('Authentication')->authenticate();
    $PMDR->get('Favorites')->replace(array('user_id'=>$PMDR->get('Session')->get('user_id'),'listing_id'=>$listing['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_favorites_added'),'insert');
}

// Remove listing from favorites
if($_GET['action'] == 'removefromfavorites') {
    $PMDR->get('Authentication')->authenticate();
    $db->Execute("DELETE FROM ".T_FAVORITES." WHERE user_id=? AND listing_id=?",array($PMDR->get('Session')->get('user_id'),$listing['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_favorites_removed'),'delete');
}

// Get listing vcard
if($_GET['action'] == 'vcard') {
    $serve = $PMDR->get('ServeFile');
    $vcard = $PMDR->get('vCard');
    $vcard->addFirstName($listing['title']);
    $vcard->addName($listing['title']);
    $vcard->addOrganization($listing['title']);
    $vcard->addAddress($listing['listing_address1'].' '.$listing['listing_address2'], $map_city, $map_state, $map_country, ($listing['zip_allow'] ? $listing['listing_zip'] : ''));
    $vcard->addTelephone($listing['phone']);
    $vcard->addFax($listing['fax']);
    $vcard->addEmail($listing['mail']);
    $vcard->addURL($listing['www']);
    $vcard->addNote($listing['url']);
    $serve->serve($listing['friendly_url'].'.vcf', $vcard->getCard());
}

// Get listing PDF
if($_GET['action'] == 'pdf') {
    $contact_information = $PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip'],'<br>');
    $contact_information .= '<br>';

    if($listing['phone'] != '') {
        $contact_information .= '<br />'.$PMDR->getLanguage('public_listing_phone').': '.$listing['phone'];
    }
    if($listing['fax'] != '') {
        $contact_information .= '<br />'.$PMDR->getLanguage('public_listing_fax').': '.$listing['fax'];
    }

    if(($description = strip_tags(Strings::nl2br_replace($listing['description']),'<br><br />')) != '') {
        $description = '<br /><br />'.$description;
    }
    $description .= '<br /><br />';

    $listing_fields = $PMDR->get('Fields')->getFieldsByCategory('listings',$listing['primary_category_id']);
    foreach($listing_fields as $key=>$field) {
        if($listing['custom_'.$field['id'].'_allow'] AND $listing['custom_'.$field['id']] != '' AND !$field['hidden']) {
            $description .= $field['name'].': '.str_replace("\n",', ',$listing['custom_'.$field['id']]).'<br />';
        }
    }

    if ($listing['www_allow'] AND !empty($listing['www'])) {
        $links .= '<br /><a href="'.$listing['www'].'">'.$PMDR->getLanguage('public_listing_www').'</a>';
    }

    if ($listing['email_allow'] AND !empty($listing['mail'])) {
        $links .= '<br /><a href="'.$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/send-message.html','listing_email.php').'">'.$PMDR->getLanguage('public_listing_send_message').'</a>';
    }

    if ($listing['email_friend_allow']) {
        $links .= '<br /><a href="'.$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/send-message-friend.html','listing_email_friend.php').'">'.$PMDR->getLanguage('public_listing_email_friend').'</a>';
    }

    /** @var TCPDF */
    $pdf = $PMDR->get('TCPDF');
    $pdf->setJPEGQuality(100);
    $pdf->SetCreator(BASE_URL);
    $pdf->SetAuthor(BASE_URL);
    $pdf->SetTitle($listing['title']);
    $pdf->SetSubject($listing['description_short']);
    $pdf->SetKeywords($listing['keywords']);
    $pdf->SetPrintHeader(false); // No header as we don't want it on every page
    $pdf->SetMargins(15, 15, 15); // left, top, right
    $pdf->SetAutoPageBreak(TRUE, 10); // margin bottom
    $pdf->setImageScale(1.5);
    $pdf->SetFooterMargin(10);
    $pdf->setFooterFont(Array("dejavusans", '', 12));
    $pdf->SetAutoPageBreak(true);
    $pdf->AddPage();
    if($PMDR->getLanguage('textdirection') == 'rtl') {
        $pdf->setRTL(true);
    }
    $pdf->SetFont('dejavusans', '', 20);
    $pdf->writeHTML($listing['title'].'<hr>');
    $pdf->SetFont('dejavusans', '', 12);
    if($PMDR->getConfig('pdf_logo') AND $listing['logo_allow']) {
        if($logo = find_file(LOGO_PATH.$listing['id'].'.*')) {
            $image_details = getimagesize($logo);
            $pdf->Image(find_file(LOGO_PATH.$listing['id'].'.*'),$pdf->GetX(),$pdf->GetY(),$pdf->pixelsToUnits($image_details[0]),'','','','N', false);
            $pdf->Ln(10);
        }
    }
    $pdf->writeHTML($contact_information.$description.$links,true);

    if($PMDR->getConfig('google_apikey') != '' AND $listing['latitude'] != '0.0000000000' AND $listing['longitude'] != '0.0000000000' AND ini_get('allow_url_fopen')) {
        $pdf->Ln(5);
        $pdf->Image($PMDR->get($PMDR->getConfig('map_type').'_Map')->getMapImageByCoords($listing['latitude'],$listing['longitude']),$pdf->GetX(),$pdf->GetY(),$pdf->pixelsToUnits(512),$pdf->pixelsToUnits(512),'','http'.(SSL_CURRENT ? 's' : '').'://maps.google.com/maps?q='.$listing['latitude'].','.$listing['longitude'],'N', false, 300);
    }

    if($PMDR->getConfig('logo')) {
        if($image_details = getimagesize(TEMP_UPLOAD_PATH.$PMDR->getConfig('logo'))) {
            $pdf_y = $pdf->getPageHeight()-$pdf->getBreakMargin()-$pdf->pixelsToUnits($image_details[1]);
            if($pdf->GetY() > $pdf_y) {
                $pdf_y = $pdf->GetY();
            }
            $pdf->Image(TEMP_UPLOAD_PATH.$PMDR->getConfig('logo'), $pdf->GetX(), $pdf_y, '', '', '', BASE_URL, 'N', false, '72', 'C');
            unset($pdf_y);
        }
    }

    $pdf->Output($listing['friendly_url'].'.pdf','I');
    exit();
}

// If we want to print, use the print.tpl template file
if ($_GET['action'] == 'print') {
    $PMDR->set('footer_file','print_footer.tpl');
    $PMDR->set('header_file','print_header.tpl');
    $PMDR->set('wrapper_file','wrapper_blank.tpl');
    $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'print.tpl');
} else {
    if(trim($listing['header_template_file']) != '') {
        $PMDR->set('header_file',$listing['header_template_file']);
    }
    if(trim($listing['footer_template_file']) != '') {
        $PMDR->set('footer_file',$listing['footer_template_file']);
    }
    if(trim($listing['wrapper_template_file']) != '') {
        $PMDR->set('wrapper_file',$listing['wrapper_template_file']);
    }
    if(trim($listing['template_file']) != '' AND $PMDR->get('Templates')->path($listing['template_file'])) {
        $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.$listing['template_file']);
    } elseif($PMDR->get('Templates')->path('listing_category_'.$listing['primary_category_id'].'.tpl')) {
        $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_category_'.$listing['primary_category_id'].'.tpl');
    } else {
        $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_default.tpl');
    }
}

if(is_null($listing_categories = $PMDR->get('Cache')->get('listings_categories'.$listing['id'], 0, 'listings_'))) {
    $listing_categories = $PMDR->get('Listings')->getCategories($listing['id'],true);
    $PMDR->get('Cache')->write('listings_categories'.$listing['id'],$listing_categories,'listings_');
}

// Set page title, and breadcrumb
foreach((array) $listing_categories[$listing['primary_category_id']]['path'] as $category_path) {
    $PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Categories')->getURL($category_path['id'],$category_path['friendly_url_path']),'text'=>$category_path['title']));
}
// Set active category used for things such as banners
$PMDR->set('active_category',array('id'=>$listing['primary_category_id'],'friendly_url_path'=>$listing_categories[$listing['primary_category_id']]['friendly_url_path']));
unset($key,$category,$category_path);

// Add the listing to the breadcrumb
$PMDR->setAddArray('breadcrumb',array('link'=>null,'text'=>$listing['title']));

$title = coalesce($PMDR->getConfig('title_listing_default'),$listing['title']);
$meta_title = coalesce($listing['meta_title'],$PMDR->getConfig('meta_title_listing_default'),$listing['title']);
$meta_description = coalesce($listing['meta_description'],$PMDR->getConfig('meta_description_listing_default'),$listing['description_short'],$listing['title'].' '.$PMDR->get('Categories')->getPathDisplay($listing_categories[$listing['primary_category_id']]['path'],' ',false).' '.$PMDR->get('Locations')->getPathDisplay($listing_locations,' ',false));
$meta_keywords = coalesce($listing['meta_keywords'],$PMDR->getConfig('meta_keywords_listing_default'),$listing['title'].' '.$PMDR->get('Categories')->getPathDisplay($listing_categories[$listing['primary_category_id']]['path'],' ',false).' '.$PMDR->get('Locations')->getPathDisplay($listing_locations,' ',false));

$meta_replace = array(
    'title'=>$listing['title'],
    'zip'=>$listing['listing_zip'],
    'location_text_1'=>$listing['location_text_1'],
    'location_text_2'=>$listing['location_text_2'],
    'location_text_3'=>$listing['location_text_3']
);
foreach($meta_replace AS $find=>$replace) {
    $title = str_replace('*'.$find.'*',$replace,$title);
    $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
    $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
    $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
}
foreach((array) $listing_categories[$listing['primary_category_id']]['path'] as $key=>$category_path) {
    $replace = '';
    if(!empty($category_path['title'])) {
        $replace = '${2}'.$category_path['title'].'${4}';
    }
    $title = preg_replace('/(\[([^\]]*))?\*category_'.($key+1).'\*(([^\]]*)\])?/',$replace,$title);
    $meta_title = preg_replace('/(\[([^\]]*))?\*category_'.($key+1).'\*(([^\]]*)\])?/',$replace,$meta_title);
    $meta_description = preg_replace('/(\[([^\]]*))?\*category_'.($key+1).'\*(([^\]]*)\])?/',$replace,$meta_description);
    $meta_keywords = preg_replace('/(\[([^\]]*))?\*category_'.($key+1).'\*(([^\]]*)\])?/',$replace,$meta_keywords);
}
foreach($listing_locations as $key=>$location) {
    $replace = '';
    if(!empty($location['title'])) {
        $replace = '${2}'.$location['title'].'${4}';
    }
    $title = preg_replace('/(\[([^\]]*))?\*location_'.($key+1).'\*(([^\]]*)\])?/',$replace,$title);
    $meta_title = preg_replace('/(\[([^\]]*))?\*location_'.($key+1).'\*(([^\]]*)\])?/',$replace,$meta_title);
    $meta_description = preg_replace('/(\[([^\]]*))?\*location_'.($key+1).'\*(([^\]]*)\])?/',$replace,$meta_description);
    $meta_keywords = preg_replace('/(\[([^\]]*))?\*location_'.($key+1).'\*(([^\]]*)\])?/',$replace,$meta_keywords);
}
$listing_fields = $PMDR->get('Fields')->getFieldsByCategory('listings',$listing['primary_category_id']);
foreach($listing_fields as $key=>$field) {
    $title = str_replace('*custom_'.$field['id'].'*',$listing['custom_'.$field['id']],$title);
    $meta_title = str_replace('*custom_'.$field['id'].'*',$listing['custom_'.$field['id']],$meta_title);
    $meta_description = str_replace('*custom_'.$field['id'].'*',$listing['custom_'.$field['id']],$meta_description);
    $meta_keywords = str_replace('*custom_'.$field['id'].'*',$listing['custom_'.$field['id']],$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

$PMDR->set('canonical_url',$listing['url']);

$template_content->set('listing_url',$listing['url']);
$template_content->set('id',$listing['id']);
$template_content->set('title',$title);
$template_content->set('description',Strings::nl2br_replace($PMDR->get('Cleaner')->unclean_html($listing['description'])));
$template_content->set('categories',$listing_categories);
$template_content->set('short_description',Strings::nl2br_replace(Strings::limit_characters($listing['description_short'],$listing['short_description_size'])));
$template_content->set('new',$PMDR->get('Listings')->ifNew($listing['date']));
$template_content->set('updated',$PMDR->get('Listings')->ifUpdated($listing['date_update']));
$template_content->set('date', $PMDR->get('Dates_Local')->formatDateTime($listing['date']));
$template_content->set('date_update', $PMDR->get('Dates_Local')->formatDateTime($listing['date_update']));
$template_content->set('hot',$PMDR->get('Listings')->ifHot($listing['rating']));

if($listing['ratings_allow']) {
    $template_content->set('rating',$listing['rating']);
    $template_content->set('votes',$listing['votes']);
    if($PMDR->getConfig('ratings_require_review') OR ($PMDR->getConfig('ratings_require_login') AND !$PMDR->get('Session')->get('user_id'))) {
        $template_content->set('rating_allowed',false);
    } else {
        $template_content->set('rating_allowed',true);
    }
}

if($sms = $PMDR->get('SMS')) {
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
        $("#send_to_phone").click(function(e) {
            e.preventDefault();
            $(\'#send_to_phone_container\').dialog({
                 width: \'auto\',
                 height: \'auto\',
                 autoOpen: true,
                 modal: true,
                 resizable: false,
                 title: '.$PMDR->get('Cleaner')->output_js($PMDR->getLanguage('public_listing_send_to_phone').' - '.$listing['title']).',
                 close: function( event, ui ) {
                    $("#call_form").show();
                    $("#call_result").html("").hide();
                    $("#call_number").val("");
                 }
            });
        });
        $(document).on(\'click\', \'#send_to_phone_send\', function(e) {
            e.preventDefault();
            if(number = $("#send_to_phone_number").val()) {
                $.ajax({
                    data: ({
                        action: \'sms_listing_details\',
                        id: '.$listing['id'].',
                        number: number
                    }),
                    success: function() {
                        $("#send_to_phone_form").hide();
                        $("#send_to_phone_result").html('.$PMDR->get('Cleaner')->output_js($PMDR->getLanguage('public_listing_send_to_phone_message_success')).'+" "+$("#send_to_phone_number").val());
                        $("#send_to_phone_result").show();
                    }
                });
                $("#send_to_phone_send").prop("disabled", true);
            } else {
                $("#send_to_phone_number").focus();
            }
        });
    });
    </script>',100);
    $template_content->set('send_to_phone', true);

    if(method_exists($sms,'connectCall') AND $listing['phone_allow'] AND !empty($listing['phone'])) {
        $PMDR->loadJavascript('
        <script type="text/javascript">
        $(document).ready(function(){
            $("#call").click(function(e) {
                e.preventDefault();
                $(\'#call_container\').dialog({
                     width: \'auto\',
                     height: \'auto\',
                     autoOpen: true,
                     modal: true,
                     resizable: false,
                     title: '.$PMDR->get('Cleaner')->output_js($PMDR->getLanguage('public_listing_call').' '.$listing['title']).',
                     close: function( event, ui ) {
                        $("#call_form").show();
                        $("#call_result").html("").hide();
                        $("#call_number").val("");
                     }
                });
            });
            $(document).on(\'click\', \'#call_send\', function(e) {
                e.preventDefault();
                if(number = $("#call_number").val()) {
                    $.ajax({
                        data: ({
                            action: \'connect_call\',
                            number1: '.$PMDR->get('Cleaner')->output_js($listing['phone']).',
                            number2: number
                        }),
                        success: function() {
                            $("#call_form").hide();
                            $("#call_result").html('.$PMDR->get('Cleaner')->output_js($PMDR->getLanguage('public_listing_call_message_success')).'+" "+$("#call_number").val());
                            $("#call_result").show();
                        }
                    });
                    $("#call_send").prop("disabled", true);
                } else {
                    $("#call_number").focus();
                }
            });
        });
        </script>',100);
        $template_content->set('call', true);
    }
}

if($listing['phone_allow']) {
    $template_content->set('phone', $listing['phone']);
}

if($listing['fax_allow']) {
    $template_content->set('fax', $listing['fax']);
}

if($listing['address_allow']) {
    $template_address = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/address.tpl');
    $template_address->set('address_line1', $listing['listing_address1']);
    $template_address->set('address_line2', $listing['listing_address2']);
    $template_address->set('city', $map_city);
    $template_address->set('state', $map_state);
    $template_address->set('country', $map_country);
    $template_address->set('zip', $listing['listing_zip']);
    $template_content->set('address',$template_address);

    $template_content->set('location_text_1', $listing['location_text_1']);
    $template_content->set('location_text_2', $listing['location_text_2']);
    $template_content->set('location_text_3', $listing['location_text_3']);
    $template_content->set('address_line1', $listing['listing_address1']);
    $template_content->set('address_line2', $listing['listing_address2']);
    foreach($listing_locations as $key=>$value) {
        $template_content->set("location_".($key+1),$value['title']);
    }

    if($listing['qrcode_allow']) {
        if($_GET['action'] == 'print') {
            $qr_code = '<img src="'.BASE_URL.'/includes/data.php?type=qrcode&content='.urlencode('https://maps.google.com/maps?q='.$PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip'],', ')).'">';
            $template_content->set('qrcode',$qr_code);
        } else {
            $qr_code = $PMDR->get('QRCode');
            $qr_code->setBarcode('https://maps.google.com/maps?q='.urlencode($PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip'],', ')), 'QRCODE');
            $template_content->set('qrcode',$qr_code->getBarcodeHTML(3, 3, 'black'));
        }
        unset($qr_code);
    }
}

if($listing['hours'] == '24') {
    $template_content->set('hours', $listing['hours']);
    $template_content->set('hours_open', true);
} elseif($listing_hours = unserialize($listing['hours'])) {
    $listing['hours'] = array();
    $days = $PMDR->get('Dates')->getWeekDays();
    $open = false;
    foreach($listing_hours AS $hour) {
        $hour = explode(' ',$hour);
        if(!$open) {
            $open = $PMDR->get('Dates_Local')->isOpen($hour[0],$hour[1],$hour[2]);
        }
        $listing['hours'][$hour[0]]['title'] = $days[$hour[0]];
        $listing['hours'][$hour[0]]['hours'][] = array(
            'start'=>$PMDR->get('Dates')->formatTime(strtotime($hour[1])),
            'end'=>$PMDR->get('Dates')->formatTime(strtotime($hour[2])),
            'start_24'=>$hour[1],
            'end_24'=>$hour[2]
        );
    }
    $template_content->set('hours', $listing['hours']);
    $template_content->set('hours_open', $open);
    unset($listing_hours,$hour);
}

$template_content->set('impressions', $listing['impressions']);
$template_content->set('report_url', BASE_URL.'/contact.php?id='.$listing['id']);

if($listing['addtofavorites_allow']) {
    if(!$db->GetOne("SELECT COUNT(*) FROM ".T_FAVORITES." WHERE user_id=? and listing_id=?",array($PMDR->get('Session')->get('user_id'),$listing['id']))) {
        $template_content->set('favorites_text',$PMDR->getLanguage('public_listing_favorites_add'));
        $template_content->set('favorites_url', BASE_URL.'/listing.php?id='.$listing['id'].'&amp;action=addtofavorites');
    } else {
        $template_content->set('favorites',true);
        $template_content->set('favorites_text',$PMDR->getLanguage('public_listing_favorites_remove'));
        $template_content->set('favorites_url', BASE_URL.'/listing.php?id='.$listing['id'].'&amp;action=removefromfavorites');
    }
}
if($listing['pdf_allow']) {
    $template_content->set('pdf_url', BASE_URL.'/listing.php?id='.$listing['id'].'&amp;action=pdf');
}
if($listing['vcard_allow']) {
    $template_content->set('vcard_url', BASE_URL.'/listing.php?id='.$listing['id'].'&amp;action=vcard');
}
if($listing['zip_allow']) {
    $template_content->set('zip',$listing['listing_zip']);
}

if($listing['logo_allow'] AND file_exists(LOGO_PATH.$listing['id'].'.'.$listing['logo_extension'])) {
    $logo_url = get_file_url_cdn(LOGO_PATH.$listing['id'].'.'.$listing['logo_extension']);
    $template_content->set('logo_url',$logo_url);
    $PMDR->set('meta_image',$logo_url);
    unset($logo_url);
} elseif($listing['www_screenshot_allow'] AND file_exists(SCREENSHOTS_PATH.$listing['id'].'.jpg')) {
    $template_content->set('logo_url',get_file_url_cdn(SCREENSHOTS_PATH.$listing['id'].'.jpg'));
} elseif($PMDR->get('Templates')->path('images/noimage.png')) {
    $template_content->set('logo_url',$PMDR->get('Templates')->urlCDN('images/noimage.png'));
}

if($listing['logo_background_allow'] AND file_exists(LOGO_BACKGROUND_PATH.$listing['id'].'.'.$listing['logo_background'])) {
    $logo_url = get_file_url_cdn(LOGO_BACKGROUND_PATH.$listing['id'].'.'.$listing['logo_background']);
    $template_content->set('logo_background_url',$logo_url);
    unset($logo_background_url);
}

if($listing['www_screenshot_allow'] AND file_exists(SCREENSHOTS_PATH.$listing['id'].'.jpg') AND $listing['www_allow'] AND !empty($listing['www']) AND $_GET['action'] != 'print') {
    $PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/qTip/jquery_qtip.js"></script>',15);
    $PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/qTip/jquery_qtip.css" />',15);
    $PMDR->setAdd('javascript_onload','
    $(document).ready(function() {
        $("#listing_www").qtip({
            style: {
                width: '.$PMDR->getConfig('website_screenshot_size').',
                tip: { corner: \'rightTop\', width: 20, height: 15 },
                classes: "qtip-light"
            },
            position: { at: \'left center\', my: \'right top\' },
            content: \'<img src="'.get_file_url_cdn(SCREENSHOTS_PATH.$listing['id'].'.jpg').'" alt="" />\'
        });
    });');
}

if($listing['email_allow'] AND !empty($listing['mail'])) {
    $template_content->set('mail_raw',$listing['mail']);
    $template_content->set('mail',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/send-message.html','listing_email.php'));
}
if($listing['classifieds_limit']) {
    $template_content->set('classifieds_count',$db->GetOne("SELECT COUNT(*) AS count FROM ".T_CLASSIFIEDS." WHERE listing_id=? AND (expire_date > NOW() OR expire_date IS NULL)",array($listing['id'])));
    $template_content->set('classifieds_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/classifieds.html','listing_classifieds.php'));
}
if($listing['images_limit']) {
    $images_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_IMAGES." WHERE listing_id=?",array($listing['id']));
    $template_content->set('images_count',$images_count);
    if($images_count) {
        if(is_null($images = $PMDR->get('Cache')->get('listings_images'.$listing['id'], 0, 'listings_'))) {
            $template_content->set('images_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/images.html','listing_images.php'));
            $images = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_IMAGES." WHERE listing_id=? ORDER BY ordering ASC LIMIT ?",array($listing['id'],intval($PMDR->getConfig('listings_images_display_limit'))));
            foreach($images as $key=>$image) {
                $images[$key]['thumb'] = get_file_url_cdn(IMAGES_THUMBNAILS_PATH.$image['id'].'.'.$image['extension']);
                $images[$key]['image'] = get_file_url_cdn(IMAGES_PATH.$image['id'].'.'.$image['extension']);
                $images[$key]['description'] = Strings::nl2br_replace($image['description']);
            }
            $PMDR->get('Cache')->write('listings_images'.$listing['id'],$images,'listings_');
        }
        $template_content->set('images',$images);
        unset($key,$images);
    }
    unset($images_count);
}
if($listing['events_limit']) {
    $events_count = $PMDR->get('Events')->getListingCount($listing['id'],true);
    $template_content->set('events_count',$events_count);
    if($events_count) {
        $template_content->set('events_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/events.html','listing_events.php'));
        if(is_null($events = $PMDR->get('Cache')->get('listings_events'.$listing['id'], 0, 'listings_'))) {
            $events = $PMDR->get('Events')->getListingEvents($listing['id'],0,intval($PMDR->getConfig('listings_events_display_limit')));
            $PMDR->get('Cache')->write('listings_events'.$listing['id'],$events,'listings_');
        }
        $template_content->set('events',$events);
        unset($events);
    }
    unset($events_count);
}
if($listing['jobs_limit']) {
    $jobs_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_JOBS." WHERE listing_id=? AND status='active'",array($listing['id']));
    $template_content->set('jobs_count',$jobs_count);
    if($jobs_count) {
        $template_content->set('jobs_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/jobs.html','listing_jobs.php'));
        if(is_null($jobs = $PMDR->get('Cache')->get('listings_jobs'.$listing['id'], 0, 'listings_'))) {
            $jobs = $db->GetAll("SELECT
                                j.id,
                                title,
                                type,
                                description_short,
                                friendly_url,
                                date,
                                phone,
                                website,
                                email,
                                contact_name
                              FROM ".T_JOBS." j
                              WHERE
                                listing_id = ? AND
                                status = 'active'
                              ORDER BY j.date DESC
                              LIMIT 0, ?",array($listing['id'],intval($PMDR->getConfig('listings_events_display_limit'))));
            foreach($jobs AS &$job) {
                $job['url'] = $PMDR->get('Events')->getURL($job['id'],$job['friendly_url']);
                $job['date'] = $PMDR->get('Dates_Local')->formatDateTime($job['date']);
            }
            $PMDR->get('Cache')->write('listings_jobs'.$listing['id'],$jobs,'listings_');
        }
        $template_content->set('jobs',$jobs);
        unset($jobs);
    }
    unset($jobs_count);
}
if($listing['blog_posts_limit']) {
    $blog_posts_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_BLOG." WHERE listing_id=? AND status='active'",array($listing['id']));
    $template_content->set('blog_posts_count',$blog_posts_count);
    if($blog_posts_count) {
        $template_content->set('blog_posts_url',$PMDR->get('Blog')->getListingPostsURL($listing['id'],$listing['friendly_url']));
        if(is_null($blog_posts = $PMDR->get('Cache')->get('listings_blog_posts'.$listing['id'], 0, 'listings_'))) {
            $blog_posts = $db->GetAll("SELECT
                                b.id,
                                title,
                                content_short,
                                friendly_url,
                                date,
                                date_updated,
                                date_publish,
                                impressions,
                                keywords,
                                image_extension
                              FROM ".T_BLOG." b
                              WHERE
                                listing_id = ? AND
                                status = 'active'
                              ORDER BY b.date DESC
                              LIMIT 0, ?",array($listing['id'],intval($PMDR->getConfig('listings_events_display_limit'))));
            foreach($blog_posts AS &$blog_post) {
                $blog_post['url'] = $PMDR->get('Blog')->getURL($blog_post['id'],$blog_post['friendly_url']);
                $blog_post['date'] = $PMDR->get('Dates_Local')->formatDateTime($blog_post['date']);
            }
            $PMDR->get('Cache')->write('listings_blog_posts'.$listing['id'],$blog_posts,'listings_');
        }
        $template_content->set('blog_posts',$blog_posts);
        unset($blog_posts);
    }
    unset($blog_posts_count);
}
if($listing['documents_limit']) {
    $documents_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_DOCUMENTS." WHERE listing_id=?",array($listing['id']));
    $template_content->set('documents_count',$documents_count);
    if($documents_count) {
        if(is_null($documents = $PMDR->get('Cache')->get('listings_documents'.$listing['id'], 0, 'listings_'))) {
            $template_content->set('documents_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/documents.html','listing_documents.php'));
            $documents = $db->GetAll("SELECT * FROM ".T_DOCUMENTS." WHERE listing_id=?",array($listing['id']));
            foreach($documents as $key=>$document) {
                $documents[$key]['download_url'] = BASE_URL.'/listing_documents.php?action=download&download_id='.$document['id'].'&id='.$listing['id'];
            }
            $PMDR->get('Cache')->write('listings_documents'.$listing['id'],$documents,'listings_');
        }
        $template_content->set('documents',$documents);
        unset($key,$documents);
    }
    unset($documents_count);
}
if($listing['print_allow']) {
    $template_content->set('print', 'JavaScript:newWindow(\''.BASE_URL.'/listing.php?id='.$listing['id'].'&amp;action=print\',\'popup\','.$PMDR->getConfig('print_window_width').','.$PMDR->getConfig('print_window_height').',\'\')');
}
if($listing['email_friend_allow']) {
    $template_content->set('email_friend', $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/send-message-friend.html','listing_email_friend.php'));
}
if($listing['reviews_allow']) {
    $reviews_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_REVIEWS." WHERE listing_id=? AND status='active'",array($listing['id']));
    $template_content->set('reviews_count',$reviews_count);
    $template_content->set('reviews_add_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/add-review.html','listing_reviews_add.php'));
    if($reviews_count) {
        $template_content->set('reviews_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/reviews.html','listing_reviews.php'));
        if(is_null($template_content_reviews = $PMDR->get('Cache')->get('listings_reviews'.$listing['id'], 0, 'listings_'))) {
            $reviews = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS r.*, rt.rating, COALESCE(NULLIF(TRIM(u.user_first_name),''),u.login) AS user_name_formatted FROM ".T_REVIEWS." r LEFT JOIN ".T_RATINGS." rt ON r.rating_id=rt.id LEFT JOIN ".T_USERS." u ON u.id=r.user_id WHERE r.listing_id=? AND r.status='active' GROUP BY r.id ORDER BY date DESC LIMIT ?",array($listing['id'],intval($PMDR->getConfig('listings_reviews_display_limit'))));
            $template_content_reviews = array();
            foreach($reviews AS $review_key=>$review) {
                $template_content_reviews[$review_key] = $PMDR->get('Reviews')->getReviewTemplate($review,$ratings_categories,isset($_GET['review_id']),$listing['user_id']);
                $PMDR->get('Fields_Groups')->addToTemplate($template_content_reviews[$review_key],$review,'reviews',$listing['primary_category_id']);
                if($review_key == 0) {
                    $template_content_reviews[$review_key]->set('javascript',true);
                }
                $template_content_reviews[$review_key] = $template_content_reviews[$review_key]->render();
            }
            unset($review_key,$review);
            $PMDR->get('Cache')->write('listings_reviews'.$listing['id'],$template_content_reviews,'listings_');
        }
        $template_content->set('reviews',$template_content_reviews);
        unset($template_content_reviews);
    }
}
if($listing['claim_allow'] AND !$listing['claimed']) {
    $template_content->set('claim_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/claim.html','listing_claim.php'));
}
if($listing['suggestion_allow']) {
    $template_content->set('suggestion_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/suggestion.html','listing_suggestion.php'));
}
if($listing['share_allow']) {
    $template_content->set('share',$PMDR->get('Sharing')->getHTML(null,null,null,false,array('action'=>'statistic','type'=>'listing_share','type_id'=>$listing['id'])));
    $template_content->set('share_url',$PMDR->get('Sharing')->getURL());
}
if($listing['social_links_allow']) {
    $PMDR->get('Sharing')->loadJavascript();
    $social_links = false;
    if(!empty($listing['facebook_page_id'])) {
        $social_links = true;
        $template_content->set('facebook_url','http://facebook.com/'.$PMDR->get('Cleaner')->clean_output($listing['facebook_page_id']));
    }
    if(!empty($listing['twitter_id'])) {
        $social_links = true;
        $template_content->set('twitter_url','http://twitter.com/'.$PMDR->get('Cleaner')->clean_output($listing['twitter_id']));
    }
    if(!empty($listing['google_page_id'])) {
        $social_links = true;
        $template_content->set('google_page_url','http://plus.google.com/'.$PMDR->get('Cleaner')->clean_output($listing['google_page_id']));
    }
    if(!empty($listing['linkedin_id'])) {
        $social_links = true;
        $template_content->set('linkedin_url','http://linkedin.com/pub/'.$PMDR->get('Cleaner')->clean_output($listing['linkedin_id']));
    }
    if(!empty($listing['linkedin_company_id'])) {
        $social_links = true;
        $template_content->set('linkedin_company_url','http://linkedin.com/company/'.$PMDR->get('Cleaner')->clean_output($listing['linkedin_company_id']));
    }
    if(!empty($listing['youtube_id'])) {
        $social_links = true;
        $template_content->set('youtube_url','http://youtube.com/user/'.$PMDR->get('Cleaner')->clean_output($listing['youtube_id']));
    }
    if(!empty($listing['pinterest_id'])) {
        $social_links = true;
        $template_content->set('pinterest_url','http://pinterest.com/'.$PMDR->get('Cleaner')->clean_output($listing['pinterest_id']));
    }
    if(!empty($listing['foursquare_id'])) {
        $social_links = true;
        $template_content->set('foursquare_url','http://foursquare.com/'.$PMDR->get('Cleaner')->clean_output($listing['foursquare_id']));
    }
    if(!empty($listing['instagram_id'])) {
        $social_links = true;
        $template_content->set('instagram_url','http://instagram.com/'.$PMDR->get('Cleaner')->clean_output($listing['instagram_id']));
    }
    $template_content->set('social_links',$social_links);
}

if($listing['www_allow'] AND !empty($listing['www'])) {
    $template_content->set('www',$listing['www']);
    if ($_GET['action'] != 'print') {
        if($PMDR->getConfig('js_click_counting')) {
            $template_content->set('www_url',$listing['www']);
            $template_content->set('www_javascript','onclick="$.ajax({async: false, cache: false, timeout: 30000, data: ({ action: \'add_click\', id: '.$listing['id'].' })});"');
        } else {
            if (MOD_REWRITE) {
                $template_content->set('www_url',BASE_URL.'/out-'.$listing['id'].'.html');
            } else {
                $template_content->set('www_url',BASE_URL.'/out.php?listing_id='.$listing['id']);
            }
        }
    }
}

$custom_fields_exclude = array();

if($PMDR->getConfig('skype_field') AND !empty($listing[$PMDR->getConfig('skype_field')]) AND $listing[$PMDR->getConfig('skype_field').'_allow']) {
    $PMDR->loadJavascript('<script type="text/javascript" src="http'.(SSL_CURRENT ? 's' : '').'://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>');
    $template_content->set('skype_url','skype:'.$listing[$PMDR->getConfig('skype_field')].'?call');
    $custom_fields_exclude[] = $PMDR->getConfig('skype_field');
}

$PMDR->get('Fields_Groups')->addToTemplate($template_content,$listing,'listings',$listing['primary_category_id'],$custom_fields_exclude);

if($listing['map_allow']) {
    /** @var Map */
    $map = $PMDR->get('Map');
    $map->disableScroll = true;
    if(!$listing['disable_geocoding']) {
        $map_variables = $map->getOutputVariables($listing['listing_address1'],$map_city,$map_state,$map_country,$listing['listing_zip'],$listing['latitude'],$listing['longitude'],$listing['title'],$_GET['action'] == 'print');
        if(isset($map_variables['latitude'])) {
            $db->Execute("UPDATE ".T_LISTINGS." SET longitude=?, latitude=? WHERE id=?",array($map_variables['longitude'],$map_variables['latitude'],$listing['id']));
        }
        $template_content->setArray($map_variables);
    }

    if($listing['latitude'] != '0.0000000000' AND $listing['longitude'] != '0.0000000000') {
        $PMDR->set('meta_geo_position',$listing['latitude'].';'.$listing['longitude']);
        $template_content->set('latitude',$listing['latitude']);
        $template_content->set('longitude',$listing['longitude']);
    }
}

if(is_null($related_listings = $PMDR->get('Cache')->get('listing_related_'.$listing['id'], 2592000))) {
    $related_listings = $db->GetAll("SELECT id, title, friendly_url, logo_extension
    FROM ".T_LISTINGS."
    WHERE MATCH(title, description_short, keywords) AGAINST(?) AND status='active' AND id!=? LIMIT 5",
    array($listing['title']." ".$listing['keywords'],$listing['id']));
    if(is_array($related_listings)) {
       foreach($related_listings AS &$related_listing) {
          $related_listing['url'] = $PMDR->get('Listings')->getURL($related_listing['id'],$related_listing['friendly_url']);
          $related_listing['logo_thumb_url'] = get_file_url_cdn(LOGO_PATH.$related_listing['id'].'.'.$related_listing['logo_extension']);
       }
    }
    $PMDR->get('Cache')->write('listing_related_'.$listing['id'],$related_listings);
}
$template_content->set('related_listings',$related_listings);
unset($related_listings);

if(is_null($user_listings = $PMDR->get('Cache')->get('user_listings_related_'.$listing['id'], 2592000))) {
    $user_listings = $db->GetAll("SELECT id, title, friendly_url
    FROM ".T_LISTINGS."
    WHERE user_id=? AND status='active' AND id!=?
    ORDER BY title ASC LIMIT 20",array($listing['user_id'],$listing['id']));
    if(is_array($user_listings)) {
       foreach($user_listings AS &$user_listing) {
          $user_listing['url'] = $PMDR->get('Listings')->getURL($user_listing['id'],$user_listing['friendly_url']);
       }
    }
    $PMDR->get('Cache')->write('user_listings_related_'.$listing['id'],$user_listings);
}
$template_content->set('user_listings',$user_listings);

if($PMDR->getConfig('listings_linked')) {
    if(is_null($linked_listings = $PMDR->get('Cache')->get('listings_linked_'.$listing['id'], 2592000))) {
        $linked_listings = $db->GetAll("SELECT l.id, l.title, l.friendly_url
        FROM ".T_LISTINGS_LINKED." ll INNER JOIN ".T_LISTINGS." l ON ll.listing_linked_id=l.id
        WHERE ll.listing_id=? AND l.status='active' AND l.id!=?
        ORDER BY title ASC",array($listing['id'],$listing['id']));
        if(is_array($linked_listings)) {
           foreach($linked_listings AS &$linked_listing) {
              $linked_listing['url'] = $PMDR->get('Listings')->getURL($linked_listing['id'],$linked_listing['friendly_url']);
           }
        }
        $PMDR->get('Cache')->write('listings_linked_'.$listing['id'],$linked_listings);
    }
    $template_content->set('listings_linked',$listings_linked);
}

if($listing['locations_limit']) {
    $locations_count = $db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS_LOCATIONS." WHERE listing_id=?",array($listing['id']));
    $template_content->set('locations_count',$locations_count);
    if($locations_count) {
        $template_content->set('locations_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/locations.html','listing_locations.php'));
        if(is_null($listings_locations = $PMDR->get('Cache')->get('listings_locations_'.$listing['id'], 2592000))) {
            $listings_locations = $db->GetAll("SELECT title, phone, url, email, formatted
            FROM ".T_LISTINGS_LOCATIONS."
            WHERE listing_id=?",array($listing['id']));
            foreach($listings_locations AS $key=>$listing_location) {
                $listings_locations[$key]['map_url'] = 'https://www.google.com/maps/place/'.urlencode(Strings::strip_new_lines($listing_location['formatted']));
            }
            unset($key,$listing_location);
            $PMDR->get('Cache')->write('listings_locations_'.$listing['id'],$listings_locations);
        }
        $template_content->set('listings_locations',$listings_locations);
    }
}

if($last_search = $PMDR->get('Session')->get('last_search')) {
    $template_content->set('last_search_url',$last_search);
}

$PMDR->set('og:data',array(
    'business:contact_data:street_address'=>$listing['listing_address1'],
    'business:contact_data:locality'=>$map_city,
    'business:contact_data:region'=>$map_state,
    'business:contact_data:postal_code'=>$listing['listing_zip'],
    'business:contact_data:country_name'=>$map_country,
    'business:contact_data:phone_number'=>$listing['phone'],
    'business:contact_data:fax_number'=>$listing['fax'],
    'business:contact_data:website'=>(!empty($listing['www']) ? $listing['www'] : $listing['url']),
));

$PMDR->get('Statistics')->insert('listing_impression',$listing['id']);

$PMDR->get('Plugins')->run_hook('listing_end');

include(PMDROOT.'/includes/template_setup.php');
?>