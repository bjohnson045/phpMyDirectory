<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_out'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_out'));

$out_disable = false;
if(LOGGED_IN) {
    $out_disable = $db->GetOne("SELECT out_disable FROM ".T_USERS." WHERE id=?",array($PMDR->get('Session')->get('user_id')));
}

$url = null;

if(isset($_GET['banner_id']) AND !empty($_GET['banner_id'])) {
    if($banner = $db->GetRow("SELECT id, url FROM ".T_BANNERS." WHERE id=?",array($_GET['banner_id']))) {
        $PMDR->get('Statistics')->insert('banner_click',$banner['id']);
        if(strstr($banner['url'],BASE_URL)) {
            $out_disable = true;
        }
        if(!isset($_GET['listing_id']) AND empty($_GET['listing_id'])) {
            if(!is_null($banner['listing_id'])) {
                $PMDR->get('Statistics')->insert('listing_banner_click',$banner['listing_id']);
            }
            $url = standardize_url($banner['url']);
        }
    }
}

if(isset($_GET['listing_id']) AND !empty($_GET['listing_id'])) {
    if($listing = $db->GetRow("SELECT id, www, www_allow, friendly_url FROM ".T_LISTINGS." WHERE id=?",array($_GET['listing_id']))) {
        $PMDR->get('Plugins')->run_hook('listing_out',$listing['id']);
        $PMDR->get('Statistics')->insert('listing_website',$listing['id']);
        if($banner AND ($PMDR->getConfig('banner_link') == "LISTING" OR $listing['www'] == '' OR !$listing['www_allow'])) {
            redirect($PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
        } else {
            $url = standardize_url($listing['www']);
        }
    }
}

if(is_null($url)) {
    redirect(BASE_URL);
}

if($PMDR->getConfig('out_warning') AND !$out_disable) {
    $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'out.tpl');
    $template_content->set('url',$url);
    $template_content->set('message',$PMDR->getLanguage('public_out_message',$PMDR->getConfig('title')));
    $form = $PMDR->getNew('Form');
    $form->addField('out_disable','checkbox',array('options'=>array(1=>$PMDR->getLanguage('public_out_disable'))));
    $template_content->set('form',$form);
    include(PMDROOT.'/includes/template_setup.php');
} else {
    redirect($url);
}
?>