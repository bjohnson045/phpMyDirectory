<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_blog'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_blog_archive'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('blog_archive_meta_title'),$PMDR->getLanguage('public_blog_archive')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('blog_archive_meta_description'),$PMDR->getLanguage('public_blog_archive')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/blog.php','text'=>$PMDR->getLanguage('public_blog')));
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('public_blog_archive')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blog_archive.tpl');

$dates = $db->GetAll("SELECT MONTH(date_publish) AS month_number, YEAR(date_publish) AS year_number, COUNT(*) AS count FROM ".T_BLOG." WHERE status='active' AND DATE(date_publish) <= CURDATE() GROUP BY year_number, month_number ORDER BY year_number DESC, month_number DESC");

foreach($dates AS &$date) {
    $date['month'] = strftime('%B',mktime(0, 0, 0, $date['month_number']));
}

$template_content->set('dates',$dates);

include(PMDROOT.'/includes/template_setup.php');
?>