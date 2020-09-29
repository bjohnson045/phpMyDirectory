<?php
include('./defaults.php');

// Load the language so CHARSET gets populated
$PMDR->loadLanguage(array('public_general'));

// Check the from variable to ensure it matches to prevent CSRF and return a 500 error that the AJAX error handler can process
if(!isset($_POST[COOKIE_PREFIX.'from']) OR empty($_POST[COOKIE_PREFIX.'from']) OR $_POST[COOKIE_PREFIX.'from'] != $_COOKIE[COOKIE_PREFIX.'from']) {
    header('HTTP/1.0 500 Internal Server Error', true, '500');
    exit('Bad Token');
}

$require_authentication = array(
    'add_quality',
    'add_comment',
    'blog_follow',
);

// Check authentication, and if not authenticated return a 500 error that the AJAX error handler can process
if(in_array($_POST['action'],$require_authentication) AND !$PMDR->get('Authentication')->authenticate(array('redirect'=>false))) {
    header('HTTP/1.0 401 Unauthorized', true, '401');
    exit(BASE_URL.MEMBERS_FOLDER.'index.php');
}

// Need to add charset to all ajax responses where we don't use common_header.php
header('Content-Type: text/html; charset='.CHARSET);

if(isset($_POST['load_language'])) {
    $PMDR->loadLanguage(array($_POST['load_language']));
}

switch($_POST['action']) {
    case 'geolocation_cache':
        $geo_data = array();
        if($result = $db->GetRow("SELECT l.country,l.region,l.city FROM ".T_MAXMIND_LOCATION." l JOIN ".T_MAXMIND_BLOCKS." b ON (l.locId=b.locId) WHERE b.endIpNum >= INET_ATON(?) order by b.endIpNum limit 1",array(get_ip_address()))) {
            $geo_data['country'] = $result['country'];
            $geo_data['city'] = $result['city'];
            $geo_data['region'] = $result['region'];
        } else {
            $geo_data = false;
        }
        if(isset($_POST['latitude']) AND isset($_POST['longitude'])) {
            $geo_data['latitude'] = $_POST['latitude'];
            $geo_data['longitude'] = $_POST['longitude'];
        }
        $_SESSION['location'] = $geo_data;
        echo json_encode($geo_data);
        break;
    case 'banner_click':
        $banner = $db->GetRow("SELECT id, listing_id FROM ".T_BANNERS." WHERE id=?",array($_POST['id']));
        if(!is_null($banner['listing_id'])) {
            $PMDR->get('Statistics')->insert('listing_banner_click',$banner['listing_id']);
        }
        $PMDR->get('Statistics')->insert('banner_click',$banner['id']);
        break;
    case 'add_click':
        $PMDR->get('Statistics')->insert('listing_website',$_POST['id']);
        break;
    case 'save_rating':
        $PMDR->loadLanguage(array('public_listing'));
        $user_id = $PMDR->get('Session')->get('user_id');
        $listing = $PMDR->get('Listings')->getRow($_POST['listing_id']);

        // Check if the user is trying to vote for their own listing.
        if ($listing['user_id'] == $user_id) {
            $PMDR->addMessage('error',$PMDR->getLanguage('public_listing_cant_vote_own'));
        } elseif($user_id AND $old_rating_id = $PMDR->get('DB')->GetOne("SELECT id FROM ".T_RATINGS." WHERE listing_id=? AND user_id=? LIMIT 1",array($_POST['listing_id'], $user_id))) {
            $PMDR->get('Ratings')->update(array('rating'=>$_POST['rating'],'listing_id'=>$_POST['listing_id']), $old_rating_id);
            $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_vote_updated'));
        } elseif(!$user_id AND $PMDR->get('Ratings')->hasVoted($_POST['listing_id'])) {
            $PMDR->addMessage('error',$PMDR->getLanguage('public_listing_rating_already_voted'));
        } else {
            $data['rating'] = $_POST['rating'];
            $data['listing_id'] = $_POST['listing_id'];
            $data['user_id'] = $PMDR->get('Session')->get('user_id') ? $PMDR->get('Session')->get('user_id') : NULL;
            $PMDR->get('Ratings')->insert($data);
            $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_vote_submitted'),'insert');
        }
        break;
    case 'add_quality':
        $data['review_id'] = $_POST['id'];
        $data['helpful'] = $_POST['helpful'];
        $data['user_id'] = $PMDR->get('Session')->get('user_id');
        $PMDR->get('Reviews_Quality')->insert($data);
        break;
    case 'add_comment':
        if(trim($_POST['comment']) != '') {
            $data['review_id'] = $_POST['id'];
            $data['comment'] = $_POST['comment'];
            $data['user_id'] = $PMDR->get('Session')->get('user_id');
            $PMDR->get('Reviews_Comments')->insert($data);
        }
        break;
    case 'rewrite':
        echo Strings::rewrite($_POST['text']);
        break;
    case 'faq_search':
        $results = $db->GetAll("SELECT id, question AS value, question AS label FROM ".T_FAQ_QUESTIONS." WHERE question LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_POST['keywords']."%")." OR answer LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_POST['keywords']."%"));
        echo json_encode($results);
        break;
    case 'sms_listing_details':
        if(!$sms = $PMDR->get('SMS')) {
            trigger_error('Invalid SMS Gateway');
        } else {
            if($listing = $db->GetRow("SELECT id, title, friendly_url, listing_address1, listing_address2, phone, zip_allow, phone_allow, listing_zip, location_id, location_text_1, location_text_2, location_text_3, www, www_allow, address_allow FROM ".T_LISTINGS." WHERE id=?",array($_POST['id']))) {
                $listing_information = $listing['title']."\n";
                if($listing['address_allow']) {
                    $listing_locations = $PMDR->get('Locations')->getPath($listing['location_id']);
                    foreach($listing_locations as $key=>$location) {
                        $listing['location_'.($key+1)] = $location['title'];
                    }
                    $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $listing[$PMDR->getConfig('map_country')];
                    $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $listing[$PMDR->getConfig('map_state')];
                    $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $listing[$PMDR->getConfig('map_city')];
                    $listing_information .= $PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip']);
                    $listing_information .= "\n";
                }
                if($listing['phone_allow'] AND !empty($listing['phone'])) {
                    $listing_information .= $listing['phone']."\n";
                }
                if($listing['www_allow'] AND !empty($listing['www'])) {
                    $listing_information .= $listing['www'];
                }
                $sms->sendMessage($_POST['number'],$listing_information);
                $PMDR->get('Statistics')->insert('listing_sms',$listing['id']);
            } else {
                trigger_error('No listing for SMS');
            }
        }
        break;
    case 'connect_call':
        $sms = $PMDR->get('SMS');
        if(empty($_POST['number1'])) {
            trigger_error('Empty phone number 1');
        } elseif(empty($_POST['number2'])) {
            trigger_error('Empty phone number 2');
        } else {
            $sms->connectCall($_POST['number1'],$_POST['number2']);
        }
        break;
    case 'out_change':
        $user_id = $PMDR->get('Session')->get('user_id');
        $db->Execute("UPDATE ".T_USERS." SET out_disable=? WHERE id=?",array($_POST['out_disable'],$user_id));
        break;
    case 'blog_follow':
        if($user_id = $PMDR->get('Session')->get('user_id')) {
            if(!$_POST['follow']) {
                $PMDR->get('Blog')->follow($_POST['id'],$user_id);
            } else {
                $PMDR->get('Blog')->unfollow($_POST['id'],$user_id);
            }
        }
        break;
    case 'map':
        $listings = $db->GetAll("SELECT id, title, latitude, longitude, primary_category_id, listing_address1, listing_address2, listing_zip, location_text_1, location_text_2, location_text_3, location_id FROM ".T_LISTINGS." WHERE latitude BETWEEN ? AND ? AND longitude BETWEEN ? and ?",array($_POST['southWestLat'],$_POST['northEastLat'],$_POST['southWestLng'],$_POST['northEastLng']));
        foreach($listings AS $key=>$listing) {
            if($file = find_file(CATEGORY_IMAGE_PATH.$listing['primary_category_id'].'-map.*')) {
                $listings[$key]['icon'] = get_file_url_cdn($file);
            }
            $listings[$key]['url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
            $listing_locations = $PMDR->get('Locations')->getPath($listing['location_id']);
            foreach($listing_locations as $location_key=>$location) {
                $listing['location_'.($location_key+1)] = $location['title'];
            }
            $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $listing[$PMDR->getConfig('map_country')];
            $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $listing[$PMDR->getConfig('map_state')];
            $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $listing[$PMDR->getConfig('map_city')];
            $map_popup = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/map_popup.tpl');
            $map_popup->set('title',$listing['title']);
            $map_popup->set('url',$listings[$key]['url']);
            $map_popup->set('address',$PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip']));
            $listings[$key]['html'] = $map_popup->render();
        }
        echo json_encode($listings);
        break;
    case 'map_events':
        $records = $db->GetAll("SELECT id, friendly_url, title, latitude, longitude, location, venue FROM ".T_EVENTS." WHERE status='active' AND date_end > NOW() AND latitude BETWEEN ? AND ? AND longitude BETWEEN ? and ?",array($_POST['southWestLat'],$_POST['northEastLat'],$_POST['southWestLng'],$_POST['northEastLng']));
        foreach($records AS $key=>$record) {
            $records[$key]['url'] = $PMDR->get('Events')->getURL($record['id'],$record['friendly_url']);
            $map_popup = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/map_popup.tpl');
            $map_popup->set('title',$record['title']);
            $map_popup->set('url',$records[$key]['url']);
            $map_popup->set('address',$record['location']);
            $records[$key]['html'] = $map_popup->render();
        }
        echo json_encode($records);
        break;
    case 'message_add':
        $template = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/message.tpl');
        $message_types = array($_POST['type']=>array($_POST['message']));
        $template->set('message_types',$message_types);
        echo $template->render();
        break;
    case 'events_calendar':
        $PMDR->loadLanguage('public_events');
        $events = $PMDR->get('Events')->getRangeTimestamp($_POST['start'],$_POST['end']);
        $data = array();
        $id = 1;
        foreach($events AS $event) {
            $data[] = array (
                'id'=>$id,
                'title'=>$event['title'],
                'start'=>$PMDR->get('Dates_Local')->formatDate($event['date_start'],'Y-m-d H:i:s'),
                'end'=>$PMDR->get('Dates_Local')->formatDate($event['date_end'],'Y-m-d H:i:s'),
                'url'=>$PMDR->get('Events')->getURL($event['id'],$event['friendly_url']),
                'color'=>$event['color'],
            );
            $id++;
        }
        echo json_encode($data);
        break;
    case 'event_rsvp':
        $PMDR->loadLanguage('email_templates');
        if($user_id = $PMDR->get('Session')->get('user_id')) {
            if(!$_POST['rsvp']) {
                $PMDR->get('Events')->rsvp($_POST['id'],$user_id);
                $PMDR->get('Email_Templates')->send('events_rsvp_new',array('event_id'=>$_POST['id']));
            } else {
                $PMDR->get('Events')->rsvpCancel($_POST['id'],$user_id);
            }
        }
        break;
    case 'statistic':
        if(is_numeric($_POST['type_id']) AND !empty($_POST['type'])) {
            $PMDR->get('Statistics')->insert($_POST['type'],$_POST['type_id']);
        }
        break;
    case 'keyword_search':
        $template = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/search_popup.tpl');
        if(!empty($_POST['value'])) {
            $query_parts = preg_replace('/\s+/', ' ',$_POST['value']);
            $query_parts = '+'.str_replace(' ',' +',trim($query_parts));
            if($listings = $db->GetAll("SELECT id, friendly_url, title FROM ".T_LISTINGS." WHERE MATCH(title) AGAINST (".$db->Clean($query_parts.'*')." IN BOOLEAN MODE) AND status='active' ORDER BY priority DESC LIMIT 10")) {
                foreach($listings AS &$listing) {
                    $listing['url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
                }
                $template->set('listings',$listings);
            }
            if($classifieds = $db->GetAll(
                "SELECT id, title, friendly_url FROM ".T_CLASSIFIEDS."
                 WHERE MATCH(title, keywords) AGAINST (".$db->Clean($query_parts.'*')." IN BOOLEAN MODE)
                 ORDER BY title ASC LIMIT 10")
            ) {
                foreach($classifieds AS &$classified) {
                    $classified['url'] = $PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']);
                }
                unset($classified);
                $template->set('classifieds',$classifieds);
            }
            if($categories = $db->GetAll("SELECT id, title, friendly_url, friendly_url_path FROM ".T_CATEGORIES." WHERE MATCH(title,keywords) AGAINST (".$db->Clean($_POST['value'].'*')." IN BOOLEAN MODE) ORDER BY title ASC LIMIT 10")) {
                foreach($categories AS &$category) {
                    $category['url'] = $PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
                }
                $template->set('categories',$categories);
            }
        }
        if($listings OR $classifieds OR $categories) {
            $template->set('results',true);
        }
        echo $template->render();
        break;
}
?>