<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_site_links'));

if(isset($_GET['action']) AND $_GET['action'] == 'display') {
    header("Content-type: application/x-javascript");
    if(!$site_link = $PMDR->get('Site_Links')->getRow($_GET['id'])) {
        exit();
    }

    $url = coalesce($site_link['url_alternate'],BASE_URL);

    if(isset($_GET['listing_id'])) {
        if(!empty($site_link['pricing_ids'])) {
            $listing = $db->GetRow("SELECT l.id, l.friendly_url, o.pricing_id, l.status FROM ".T_LISTINGS." l, ".T_ORDERS." o WHERE l.id=? AND o.pricing_id IN (".$site_link['pricing_ids'].")",array($_GET['listing_id']));
        } else {
            $listing = $db->GetRow("SELECT l.id, l.friendly_url, o.pricing_id, l.status FROM ".T_LISTINGS." l, ".T_ORDERS." o WHERE l.id=?",array($_GET['listing_id']));
        }
        if(!$listing) {
            exit();
        }
        if(empty($site_link['url_alternate_listing'])) {
            $url = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
        } else {
            $url = $site_link['url_alternate_listing'];
        }
        if($listing['status'] != 'active') {
            if($site_link['requires_active_product']) {
                exit();
            }
            if(!empty($site_link['url_alternate_inactive'])) {
                $url = $site_link['url_alternate_inactive'];
            }
        }
    }

    $content = '';
    if($site_link['extension']) {
        $content .= '<a href="'.$url.'"><img src="'.get_file_url(SITE_LINKS_PATH.$site_link['id'].'.'.$site_link['extension']).'" alt="" /></a>';
    }
    if($site_link['link_text']) {
        if($site_link['extension']) {
            $content .= '<br />';
        }
        $content .= '<a href="'.$url.'">'.$PMDR->get('Cleaner')->clean_output($site_link['link_text']).'</a>';
    }
    if($site_link['show_date']) {
        $content .= '<br />'.date('m-d-Y');
    }
    echo "document.write('".$content."');";
    exit();
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_site_links'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('site_links_meta_title'),$PMDR->getLanguage('site_links_contact')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('site_links_meta_description'),$PMDR->getLanguage('site_links_contact')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/site_links.php','text'=>$PMDR->getLanguage('public_site_links')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'site_links.tpl');

$template_content->expire = 900;
if(isset($_GET['listing_id'])) {
    $template_content->cache_id = 'site_links'.intval($_GET['listing_id']);
} else {
    $template_content->cache_id = 'site_links';
}
if(!$template_content->isCached()) {
    if(isset($_GET['listing_id'])) {
        $links = $PMDR->get('Site_Links')->getLinks($_GET['listing_id']);
    } else {
        $links = $PMDR->get('Site_Links')->getLinks();
    }
    $template_content->set('links',$links);
}

include(PMDROOT.'/includes/template_setup.php');
?>