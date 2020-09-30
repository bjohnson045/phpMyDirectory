<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_jobs','email_templates'));

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['jobs_limit']) {
    $PMDR->get('Error',404);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_jobs'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_jobs'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_jobs'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_jobs'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_jobs'),'listing_title'=>$listing['title']);
foreach($meta_replace AS $find=>$replace) {
    $title = str_replace('*'.$find.'*',$replace,$title);
    $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
    $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
    $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),'text'=>$listing['title']));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_jobs')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_jobs.tpl');

$paging = $PMDR->get('Paging');
$records = $db->GetAll("SELECT
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
                  LIMIT ?, ?",array($listing['id'],$paging->limit1,$paging->limit2));
foreach($records as $key=>$record) {
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    $records[$key]['url'] = $PMDR->get('Jobs')->getURL($record['id'],$record['friendly_url']);
}
$template_content->set('records',$records);
$paging->setTotalResults($db->FoundRows());

$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>